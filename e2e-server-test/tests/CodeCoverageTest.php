<?php
require_once __DIR__.'/BaseTest.php';
class CodeCoverageTest extends BaseTest
{
    public function runTest()
    {
        if ($this->os == 'AlmaLinux-9.4') {

            $this->sshExec('export NON_INT=1', true, 8000);
            $this->sshExec('wget -q -O - http://www.atomicorp.com/installers/atomic | sh', true, 8000);

            $this->sshExec('dnf -y install git rpm-build gcc make', true);
            $this->sshExec('dnf group install "Development Tools" -y', true);
            $this->sshExec('dnf install tar -y', true);
            $this->sshExec('dnf install python3 python3-devel -y', true);
        } else {
            $this->sshExec('apt-get install autoconf build-essential -y', true);
        }

        $this->sshExec('mkdir -p /usr/local/omega/php-xdebug', true);
        $this->sshExec("cd /usr/local/omega/php-xdebug \n wget http://xdebug.org/files/xdebug-3.3.2.tgz", true);
        $this->sshExec("cd /usr/local/omega/php-xdebug \n tar -xvzf xdebug-3.3.2.tgz", true);


        $this->sshExec("cd /usr/local/omega/php-xdebug/xdebug-3.3.2 \n /usr/local/omega/php/bin/phpize", true);
        sleep(3);
        $this->sshExec("cd /usr/local/omega/php-xdebug/xdebug-3.3.2 \n ./configure --enable-xdebug --with-php-config=/usr/local/omega/php/bin/php-config", true);
        sleep(3);
        $this->sshExec("cd /usr/local/omega/php-xdebug/xdebug-3.3.2 \n make", true);
        sleep(3);

        $this->sshExec('mkdir -p /usr/local/omega/php/zend-xdebug', true);
        $this->sshExec('cp /usr/local/omega/php-xdebug/xdebug-3.3.2/modules/xdebug.so /usr/local/omega/php/zend-xdebug/xdebug.so', true);

        $this->sshExec('chmod 777 /usr/local/omega/php/zend-xdebug/xdebug.so', true);
        $this->sshExec('cp /usr/local/omega/web/tests/Configs/xDebugPHP.ini /usr/local/omega/php/bin/php.ini', true);
        $this->sshExec('chmod 777 /usr/local/omega/php/bin/php.ini', true);

        $this->sshExec('omega-php -v', true);

        $this->sshExec('chmod -R 777 /usr/local/omega/web/vendor', true);

        if ($this->os == 'AlmaLinux-9.4') {
            $this->sshExec('dnf install composer -y', true);
        } else {
            $this->sshExec('apt-get install composer -y', true);
        }

        $this->sshExec("cd /usr/local/omega/web/ \n omega-php artisan omega:set-ini-settings APP_ENV 'local'", true);
        $this->sshExec("cd /usr/local/omega/web/ \n composer test-coverage", true);

        $this->sshExec('mv /usr/local/omega/web/clover.xml Panel/clover.xml', true);
        $this->sshExec('mv /usr/local/omega/web/coverage.xml Panel/coverage.xml', true);

        if ($this->os == 'AlmaLinux-9.4') {
            $this->sshExec('dnf install -yq python3-pip', true);
        } else {
            $this->sshExec('apt-get install -yq python3-pip', true);
        }

        $this->sshExec('pip install codecov-cli', true);

        $this->sshExec("cd Panel \n codecovcli --verbose upload-process -t $this->codecovToken", true);

    }
}
