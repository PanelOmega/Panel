#!/bin/bash

INSTALL_DIR="/omega/install"

apt-get update && apt-get install ca-certificates -y

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
    "mysql-client"
    "lsb-release"
    "gnupg2"
    "ca-certificates"
    "apt-transport-https"
    "software-properties-common"
    "supervisor"
    "libonig-dev"
    "libzip-dev"
    "libcurl4-openssl-dev"
    "libsodium23"
    "libpq5"
    "libssl-dev"
    "zlib1g-dev"
)
# Check if the dependencies are installed
for DEPENDENCY in "${DEPENDENCIES_LIST[@]}"; do
    apt-get install -y $DEPENDENCY
done

# Start MySQL
service mysql start

wget https://raw.githubusercontent.com/PanelOmega/Panel/stable/installers/ubuntu-20.04/greeting.sh
mv greeting.sh /etc/profile.d/omega-greeting.sh

# Install OMEGA PHP
wget https://github.com/PanelOmega/Dist/raw/main/compilators/debian/php/dist/omega-php-8.2.0-ubuntu-20.04.deb
dpkg -i omega-php-8.2.0-ubuntu-20.04.deb

# Install OMEGA NGINX
wget https://github.com/PanelOmega/Dist/raw/main/compilators/debian/nginx/dist/omega-nginx-1.24.0-ubuntu-20.04.deb
dpkg -i omega-nginx-1.24.0-ubuntu-20.04.deb

service omega start

OMEGA_PHP=/usr/local/omega/php/bin/php

ln -s $OMEGA_PHP /usr/bin/omega-php
#!/bin/bash

HOSTNAME=$(hostname)
IP_ADDRESS=$(hostname -I | cut -d " " -f 1)

DISTRO_VERSION=$(cat /etc/os-release | grep -w "VERSION_ID" | cut -d "=" -f 2)
DISTRO_VERSION=${DISTRO_VERSION//\"/} # Remove quotes from version string

DISTRO_NAME=$(cat /etc/os-release | grep -w "NAME" | cut -d "=" -f 2)
DISTRO_NAME=${DISTRO_NAME//\"/} # Remove quotes from name string

LOG_JSON='{"os": "'$DISTRO_NAME-$DISTRO_VERSION'", "host_name": "'$HOSTNAME'", "ip": "'$IP_ADDRESS'"}'

curl -s https://panelomega.com/api/omega-installation-log -X POST -H "Content-Type: application/json" -d "$LOG_JSON"
#!/bin/bash

wget https://github.com/PanelOmega/WebCompiledVersions/raw/main/panel-omega-latest.zip
unzip -qq -o panel-omega-latest.zip -d /usr/local/omega/web
rm -rf panel-omega-latest.zip

chmod 711 /home
chmod -R 750 /usr/local/omega
#!/bin/bash

# Check dir exists
if [ ! -d "/usr/local/omega/web" ]; then
  echo "PanelOmega directory not found."
  return 1
fi

# Go to web directory
cd /usr/local/omega/web

# Create MySQL user
MYSQL_OMEGA_ROOT_USERNAME="omega"
MYSQL_OMEGA_ROOT_PASSWORD="$(tr -dc a-za-z0-9 </dev/urandom | head -c 32; echo)"

mysql -uroot -proot <<MYSQL_SCRIPT
  CREATE USER '$MYSQL_OMEGA_ROOT_USERNAME'@'%' IDENTIFIED BY '$MYSQL_OMEGA_ROOT_PASSWORD';
  GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_OMEGA_ROOT_USERNAME'@'%' WITH GRANT OPTION;
  FLUSH PRIVILEGES;
MYSQL_SCRIPT


# Create database
PANEL_OMEGA_DB_PASSWORD="$(tr -dc a-za-z0-9 </dev/urandom | head -c 32; echo)"
PANEL_OMEGA_DB_NAME="omega_$(tr -dc a-za-z0-9 </dev/urandom | head -c 13; echo)"
PANEL_OMEGA_DB_USER="omega_$(tr -dc a-za-z0-9 </dev/urandom | head -c 13; echo)"

mysql -uroot -proot <<MYSQL_SCRIPT
  CREATE DATABASE $PANEL_OMEGA_DB_NAME;
  CREATE USER '$PANEL_OMEGA_DB_USER'@'localhost' IDENTIFIED BY '$PANEL_OMEGA_DB_PASSWORD';
  GRANT ALL PRIVILEGES ON $PANEL_OMEGA_DB_NAME.* TO '$PANEL_OMEGA_DB_USER'@'localhost';
  FLUSH PRIVILEGES;
MYSQL_SCRIPT

mysql_secure_installation --use-default

# Change mysql root password
MYSQL_ROOT_PASSWORD="$(tr -dc a-za-z0-9 </dev/urandom | head -c 32; echo)"
mysql -uroot -proot <<MYSQL_SCRIPT
  ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password by '$MYSQL_ROOT_PASSWORD';
  FLUSH PRIVILEGES;
MYSQL_SCRIPT

# Save mysql root password
echo "$MYSQL_ROOT_PASSWORD" > /root/.mysql_root_password

# Configure the application
omega-php artisan omega:set-ini-settings APP_ENV "local"
omega-php artisan omega:set-ini-settings APP_URL "127.0.0.1:8443"
omega-php artisan omega:set-ini-settings APP_NAME "PANEL_OMEGA"
omega-php artisan omega:set-ini-settings DB_DATABASE "$PANEL_OMEGA_DB_NAME"
omega-php artisan omega:set-ini-settings DB_USERNAME "$PANEL_OMEGA_DB_USER"
omega-php artisan omega:set-ini-settings DB_PASSWORD "$PANEL_OMEGA_DB_PASSWORD"
omega-php artisan omega:set-ini-settings DB_CONNECTION "mysql"
omega-php artisan omega:set-ini-settings MYSQL_ROOT_USERNAME "$MYSQL_OMEGA_ROOT_USERNAME"
omega-php artisan omega:set-ini-settings MYSQL_ROOT_PASSWORD "$MYSQL_OMEGA_ROOT_PASSWORD"
omega-php artisan omega:key-generate

omega-php artisan migrate
omega-php artisan db:seed

omega-php artisan omega:set-ini-settings APP_ENV "production"

chmod -R o+w /usr/local/omega/web/storage/
chmod -R o+w /usr/local/omega/web/bootstrap/cache/

CURRENT_IP=$(hostname -I | awk '{print $1}')

echo "PanelOmega downloaded successfully."
echo "Please visit http://$CURRENT_IP:8443 to continue installation of the panel."
