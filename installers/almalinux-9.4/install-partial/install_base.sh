#!/bin/bash

INSTALL_DIR="/omega/install"

yum update -y

mkdir -p $INSTALL_DIR

cd $INSTALL_DIR

DEPENDENCIES_LIST=(
    "openssl"
    "jq"
    "curl"
    "wget"
    "unzip"
    "zip"
    "tar"
    "mysql-common"
    "mysql-server"
    "lsb-release"
    "gnupg2"
    "ca-certificates"
    "apt-transport-https"
    "software-properties-common"
    "supervisor"
    "libsodium"
    "libsodium-devel"
)
# Check if the dependencies are installed
for DEPENDENCY in "${DEPENDENCIES_LIST[@]}"; do
    dnf install -y $DEPENDENCY
done
#
## Start MySQL
systemctl start mysqld
systemctl enable mysqld
#
wget https://raw.githubusercontent.com/PanelOmega/Panel/main/installers/almalinux-9.4/greeting.sh
mv greeting.sh /etc/profile.d/omega-greeting.sh

#
## Install OMEGA PHP
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/php/dist/omega-php-8.2-1.el9.x86_64.rpm
dnf install -y omega-php-8.2-1.el9.x86_64.rpm
#
## Install OMEGA NGINX
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/nginx/dist/omega-nginx-1.25.5-1.el9.x86_64.rpm
dnf install -y omega-nginx-1.25.5-1.el9.x86_64.rpm

#
#service omega start
#
OMEGA_PHP=/usr/local/omega/php/bin/php

ln -s $OMEGA_PHP /usr/bin/omega-php

omega-php -v
