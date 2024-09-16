GIT_BRANCH="stable"
if [ -n "$1" ]; then
    GIT_BRANCH=$1
fi

INSTALL_DIR="/omega/install"

yum update -y
dnf -y install sudo wget
export NON_INT=1
sudo wget -q -O - http://www.atomicorp.com/installers/atomic | sh
dnf install epel-release -y
dnf config-manager --set-enabled epel
dnf config-manager --set-enabled crb
yum install -y libsodium libsodium-devel

mkdir -p $INSTALL_DIR

cd $INSTALL_DIR

DEPENDENCIES_LIST=(
    "apg"
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
wget https://raw.githubusercontent.com/PanelOmega/Panel/$GIT_BRANCH/installers/almalinux-9.4/greeting.sh
mv greeting.sh /etc/profile.d/omega-greeting.sh

wget https://raw.githubusercontent.com/PanelOmega/Panel/$GIT_BRANCH/installers/almalinux-9.4/repos/omega.repo
mv omega.repo /etc/yum.repos.d/omega.repo

dnf install -y omega-php
dnf install -y omega-nginx
dnf install -y my-apache

## Install OMEGA PHP
#wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/php/dist/omega-php-8.2-1.el9.x86_64.rpm
#dnf install -y omega-php-8.2-1.el9.x86_64.rpm

#
## Install OMEGA NGINX
#wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/nginx/dist/omega-nginx-1.25.5-1.el9.x86_64.rpm
#dnf install -y omega-nginx-1.25.5-1.el9.x86_64.rpm

OMEGA_PHP=/usr/local/omega/php/bin/php
ln -s $OMEGA_PHP /usr/bin/omega-php

ln -s /usr/local/omega/web/omega-shell.sh /usr/bin/omega-shell
chmod +x /usr/local/omega/web/omega-shell.sh


ln -s /usr/local/omega/web/omega-cli.sh /usr/bin/omega-cli
chmod +x /usr/local/omega/web/omega-cli.sh
