<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/tests/CommitTest.php';
require_once __DIR__.'/tests/CodeCoverageTest.php';

use phpseclib3\Crypt\RSA;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputOption;

$application = new Application();
$application->setName('PhyrePanel E2E Test');
$application->setVersion('1.0.0');
$application->register('test')
    ->addOption('HETZNER_API_KEY', null, InputOption::VALUE_REQUIRED)
    ->addOption('GIT_REPO_URL', null, InputOption::VALUE_REQUIRED)
    ->addOption('GIT_BRANCH', null, InputOption::VALUE_REQUIRED)
    ->addOption('GIT_COMMIT', null, InputOption::VALUE_REQUIRED)
    ->addOption('CODECOV_TOKEN', null, InputOption::VALUE_OPTIONAL)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {

        $gitCommit = $input->getOption('GIT_COMMIT');
        $codecovToken = $input->getOption('CODECOV_TOKEN');
        $gitCommit = substr($gitCommit, 0, 12);
        $serverNamePrefix = 'omega-test-commit-';
        $serverName = $serverNamePrefix . $gitCommit;

        $hetznerSSHName = 'omega-e2e-test-' . $gitCommit;
        $privateSSHKeyFile = 'omega-e2e-test-'.$gitCommit.'.key';
        $publicSSHKeyFile = 'omega-e2e-test-'.$gitCommit.'.pub';


//        $commitTest = new CodeCoverageTest([
//            'gitRepoUrl' => $input->getOption('GIT_REPO_URL'),
//            'gitBranch' => $input->getOption('GIT_BRANCH'),
//            'gitCommit' => $gitCommit,
//            'serverIp' => '78.46.217.196',
//            'privateSSHKeyFile' => __DIR__.'/'.$privateSSHKeyFile,
//            'codecovToken' => $codecovToken,
//        ]);
//        $testStatus = $commitTest->runTest();
//        if (isset($testStatus['testPassed']) && $testStatus['testPassed'] === true) {
//            return Command::SUCCESS;
//        }
//
//        return Command::FAILURE;


        $privateKeyGenerator = RSA::createKey(4096);
        $privateKeyContent = $privateKeyGenerator->toString('openssh');
        $publicKeyContent = $privateKeyGenerator->getPublicKey()->toString('openssh');

        file_put_contents(__DIR__.'/'.$privateSSHKeyFile, $privateKeyContent);
        file_put_contents(__DIR__.'/'.$publicSSHKeyFile, $publicKeyContent);

        $findSSHKey = false;
        $hetznerClient = new \LKDev\HetznerCloud\HetznerAPIClient($input->getOption('HETZNER_API_KEY'));

        $getSSHKeys = $hetznerClient->sshKeys()->all();
        if (!empty($getSSHKeys)) {
            foreach ($getSSHKeys as $sshKey) {
                if (str_contains($sshKey->name, 'omega-e2e-test-')) {
                    $sshKey->delete();
                }
            }
        }
        if (!$findSSHKey) {
            $hetznerClient->sshKeys()->create($hetznerSSHName, file_get_contents($publicSSHKeyFile));
        }

        foreach ($hetznerClient->servers()->all() as $server) {
            if (str_contains($server->name, $serverNamePrefix)) {
                $getServer = $hetznerClient->servers()->get($server->id);
                $getServer->delete();
                echo 'Deleting server: '.$server->name.PHP_EOL;
                continue;
            }
            echo 'ID: '.$server->id.' Name:'.$server->name.' Status: '.$server->status.PHP_EOL;
        }


        $serverTypeId = 1;
        foreach ($hetznerClient->serverTypes()->all() as $serverType) {
            if ($serverType->name == 'cx52') {
                $serverTypeId = $serverType->id;
            }
        }

//
        $serverType = $hetznerClient->serverTypes()->get($serverTypeId);
        $location = $hetznerClient->locations()->getByName('fsn1');
        $image = $hetznerClient->images()->getByName('almalinux-9');
        $apiResponse = $hetznerClient->servers()->createInLocation($serverName, $serverType, $image, $location, [$hetznerSSHName]);
        $server = $apiResponse->getResponsePart('server');
        $action = $apiResponse->getResponsePart('action');
        $nextActions = $apiResponse->getResponsePart('next_actions');

        echo 'Server: '.$server->name.PHP_EOL;
        echo 'IP: '.$server->publicNet->ipv4->ip.PHP_EOL;
//        echo 'Password: '.$apiResponse->getResponsePart('root_password').PHP_EOL;
        echo 'Now we wait on the success of the server creation!'.PHP_EOL;
        echo date('H:i:s').PHP_EOL;

        $action->waitUntilCompleted();
        foreach ($nextActions as $nextAction) {
            $nextAction->waitUntilCompleted();
        }

        echo 'Server created!'.PHP_EOL;
        echo date('H:i:s').PHP_EOL;

        sleep(30);

        $testParams = [
            'gitRepoUrl' => $input->getOption('GIT_REPO_URL'),
            'gitBranch' => $input->getOption('GIT_BRANCH'),
            'gitCommit' => $gitCommit,
            'serverIp' => $server->publicNet->ipv4->ip,
            'privateSSHKeyFile' => __DIR__ . '/' . $privateSSHKeyFile,
            'codecovToken' => $codecovToken,
        ];

        $passStages = [];
        $commitTest = new CommitTest($testParams);
        $testStatus = $commitTest->runTest();
        if (isset($testStatus['testPassed']) && $testStatus['testPassed'] === true) {
            $passStages[] = 'Commit Test';
        }

//        $commitTest = new CodeCoverageTest($testParams);
//        $codecovStatus = $commitTest->runTest();
//        if (isset($codecovStatus['testPassed']) && $codecovStatus['testPassed'] === true) {
//            $passStages[] = 'Code Coverage Test';
//        }

        $server = $hetznerClient->servers()->get($server->id);
        $delete = $server->delete();

        if (count($passStages) === 1) {
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    });

$application->run();
