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

CURRENT_IP=$(curl -s ipinfo.io/ip)

echo "PanelOmega downloaded successfully."
echo "Please visit http://$CURRENT_IP:8443 to continue installation of the panel."
