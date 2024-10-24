GIT_BRANCH="stable"
if [ -n "$1" ]; then
    GIT_BRANCH=$1
fi

# Check dir exists
if [ ! -d "/usr/local/omega/web" ]; then
  echo "PanelOmega directory not found."
  return 1
fi

# Go to web directory
cd /usr/local/omega/web

# Create MySQL OMEGA user
MYSQL_OMEGA_ROOT_USERNAME="omega"
MYSQL_OMEGA_ROOT_PASSWORD="$(apg -a 1 -m 50 -x 50 -M NCL -n 1)"

mysql -u root <<MYSQL_SCRIPT
  CREATE USER "$MYSQL_OMEGA_ROOT_USERNAME"@"%" IDENTIFIED BY "$MYSQL_OMEGA_ROOT_PASSWORD";
  GRANT ALL PRIVILEGES ON *.* TO "$MYSQL_OMEGA_ROOT_USERNAME"@"%" WITH GRANT OPTION;
  FLUSH PRIVILEGES;
MYSQL_SCRIPT

# Create database
PANEL_OMEGA_DB_PASSWORD="$(apg -a 1 -m 50 -x 50 -M NCL -n 1)"
PANEL_OMEGA_DB_NAME="omega_$(tr -dc a-za-z0-9 </dev/urandom | head -c 13; echo)"
PANEL_OMEGA_DB_USER="omega_$(tr -dc a-za-z0-9 </dev/urandom | head -c 13; echo)"

mysql -u root <<MYSQL_SCRIPT
  CREATE DATABASE $PANEL_OMEGA_DB_NAME;
  CREATE USER '$PANEL_OMEGA_DB_USER'@'localhost' IDENTIFIED BY "$PANEL_OMEGA_DB_PASSWORD";
  GRANT ALL PRIVILEGES ON $PANEL_OMEGA_DB_NAME.* TO '$PANEL_OMEGA_DB_USER'@'localhost';
  FLUSH PRIVILEGES;
MYSQL_SCRIPT


# Change mysql root password
MYSQL_ROOT_PASSWORD="$(apg -a 1 -m 50 -x 50 -M NCL -n 1)"
mysql -u root <<MYSQL_SCRIPT
  ALTER USER 'root'@'localhost' IDENTIFIED BY "$MYSQL_ROOT_PASSWORD";
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

omega-cli run-repair

service omega start

CURRENT_IP=$(hostname -I | awk '{print $1}')

echo "PanelOmega downloaded successfully."
echo "Please visit https://$CURRENT_IP:8443 to continue installation of the panel."
