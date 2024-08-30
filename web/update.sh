#!/bin/bash

rm -rf omega-nginx-1.25.5-1.el9.x86_64.rpm
rm -rf omega-php-8.2-1.el9.x86_64.rpm

wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/nginx/dist/omega-nginx-1.25.5-1.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/php/dist/omega-php-8.2-1.el9.x86_64.rpm

systemctl stop omega-php
systemctl stop omega-nginx
systemctl stop omega

dnf remove -y "omega-nginx*" "omega-php*"

dnf install -y omega-nginx-1.25.5-1.el9.x86_64.rpm
dnf install -y omega-php-8.2-1.el9.x86_64.rpm

systemctl start omega

sudo COMPOSER_ALLOW_SUPERUSER=1 omega-php composer.phar install
omega-php artisan migrate
