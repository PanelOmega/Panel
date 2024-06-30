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

$application->register('generate-ssh')
    ->addOption('HETZNER_API_KEY', null, InputOption::VALUE_REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {

        $hetznerSSHName = 'OmegaUnitTest';
        $privateSSHKeyFile = 'OmegaUnitTest.key';
        $publicSSHKeyFile = 'OmegaUnitTest.pub';

        $privateKeyGenerator = RSA::createKey(4096);
        $privateKeyContent = $privateKeyGenerator->toString('openssh');
        $publicKeyContent = $privateKeyGenerator->getPublicKey()->toString('openssh');

        file_put_contents(__DIR__.'/'.$privateSSHKeyFile, $privateKeyContent);
        file_put_contents(__DIR__.'/'.$publicSSHKeyFile, $publicKeyContent);

//        $hetznerClient = new \LKDev\HetznerCloud\HetznerAPIClient($input->getOption('HETZNER_API_KEY'));
//
//        $getSSHKeys = $hetznerClient->sshKeys()->all();
//        if (!empty($getSSHKeys)) {
//            foreach ($getSSHKeys as $sshKey) {
//                if (str_contains($sshKey->name, 'OmegaUnitTest')) {
//                    $sshKey->delete();
//                }
//            }
//        }
//        $hetznerClient->sshKeys()->create($hetznerSSHName, file_get_contents($publicSSHKeyFile));

        return Command::SUCCESS;
    });

$application->register('test')
    ->addOption('OS', null, InputOption::VALUE_REQUIRED)
    ->addOption('HETZNER_API_KEY', null, InputOption::VALUE_REQUIRED)
    ->addOption('GIT_REPO_URL', null, InputOption::VALUE_REQUIRED)
    ->addOption('GIT_BRANCH', null, InputOption::VALUE_REQUIRED)
    ->addOption('GIT_COMMIT', null, InputOption::VALUE_REQUIRED)
    ->addOption('CODECOV_TOKEN', null, InputOption::VALUE_OPTIONAL)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {

        $os = $input->getOption('OS');
        $gitCommit = $input->getOption('GIT_COMMIT');
        $codecovToken = $input->getOption('CODECOV_TOKEN');
        $gitCommit = substr($gitCommit, 0, 12);

        $serverName = 'OmegaUnitTest';
        $hetznerSSHName = 'OmegaUnitTest';
        $privateSSHKeyFile = 'OmegaUnitTest.key';
        $publicSSHKeyFile = 'OmegaUnitTest.pub';

        $hetznerClient = new \LKDev\HetznerCloud\HetznerAPIClient($input->getOption('HETZNER_API_KEY'));

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




        $serverTypeId = 1;
        foreach ($hetznerClient->serverTypes()->all() as $serverType) {
            //cx52
            if ($serverType->name == 'cpx21') {
                $serverTypeId = $serverType->id;
            }
        }

//
        $serverType = $hetznerClient->serverTypes()->get($serverTypeId);
        $location = $hetznerClient->locations()->getByName('fsn1');

        if ($os == 'AlmaLinux-9.4') {
            $image = $hetznerClient->images()->getByName('alma-9');
        } else {
            $image = $hetznerClient->images()->getByName('ubuntu-22.04');
        }

        $serverIsFound = false;
        $serverId = null;
        foreach ($hetznerClient->servers()->all() as $server) {
            if ($server->name == $serverName) {
                echo 'ID: '.$server->id.' Name:'.$server->name.' Status: '.$server->status.PHP_EOL;
                $serverIsFound = true;
                $serverId = $server->id;
                break;
            }
        }

        if (!$serverIsFound) {
            $apiResponse = $hetznerClient->servers()->createInLocation($serverName, $serverType, $image, $location, [$hetznerSSHName]);
            $server = $apiResponse->getResponsePart('server');
            $action = $apiResponse->getResponsePart('action');
            $nextActions = $apiResponse->getResponsePart('next_actions');

            echo 'Server: ' . $server->name . PHP_EOL;
            echo 'IP: ' . $server->publicNet->ipv4->ip . PHP_EOL;
//        echo 'Password: '.$apiResponse->getResponsePart('root_password').PHP_EOL;
            echo 'Now we wait on the success of the server creation!' . PHP_EOL;
            echo date('H:i:s') . PHP_EOL;

            $action->waitUntilCompleted();
            foreach ($nextActions as $nextAction) {
                $nextAction->waitUntilCompleted();
            }

            echo 'Server created!' . PHP_EOL;
            echo date('H:i:s') . PHP_EOL;

            sleep(30);
        } else {
            $getServer = $hetznerClient->servers()->get($serverId);
            // Rebuild server
            echo 'Rebuilding server' . PHP_EOL;
            $getServer->rebuildFromImage($image);

            sleep(40);
        }

        $testParams = [
            'gitRepoUrl' => $input->getOption('GIT_REPO_URL'),
            'gitBranch' => $input->getOption('GIT_BRANCH'),
            'gitCommit' => $gitCommit,
            'serverIp' => $server->publicNet->ipv4->ip,
            'privateSSHKeyFile' => __DIR__ . '/' . $privateSSHKeyFile,
            'codecovToken' => $codecovToken,
            'os' => $os,
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

        if (count($passStages) === 1) {
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    });

$application->run();
