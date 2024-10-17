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

systemctl start httpd
systemctl enable httpd

OMEGA_PHP=/usr/local/omega/php/bin/php
ln -s $OMEGA_PHP /usr/bin/omega-php
HOSTNAME=$(hostname)
IP_ADDRESS=$(hostname -I | cut -d " " -f 1)

DISTRO_VERSION=$(cat /etc/os-release | grep -w "VERSION_ID" | cut -d "=" -f 2)
DISTRO_VERSION=${DISTRO_VERSION//\"/} # Remove quotes from version string

DISTRO_NAME=$(cat /etc/os-release | grep -w "NAME" | cut -d "=" -f 2)
DISTRO_NAME=${DISTRO_NAME//\"/} # Remove quotes from name string

LOG_JSON='{"os": "'$DISTRO_NAME-$DISTRO_VERSION'", "host_name": "'$HOSTNAME'", "ip": "'$IP_ADDRESS'"}'

curl -s https://panelomega.com/api/omega-installation-log -X POST -H "Content-Type: application/json" -d "$LOG_JSON"
GIT_BRANCH="stable"
if [ -n "$1" ]; then
    GIT_BRANCH=$1
fi

wget https://github.com/PanelOmega/WebCompiledVersions/raw/main/panel-omega-latest.zip
unzip -qq -o panel-omega-latest.zip -d /usr/local/omega/web
rm -rf panel-omega-latest.zip

chmod 711 /home
chmod -R 750 /usr/local/omega

ln -s /usr/local/omega/web/omega-shell.sh /usr/bin/omega-shell
chmod +x /usr/local/omega/web/omega-shell.sh

ln -s /usr/local/omega/web/omega-cli.sh /usr/bin/omega-cli
chmod +x /usr/local/omega/web/omega-cli.sh

mkdir -p /usr/local/omega/ssl
cp /usr/local/omega/web/server/ssl/omega.crt /usr/local/omega/ssl/omega.crt
cp /usr/local/omega/web/server/ssl/omega.key /usr/local/omega/ssl/omega.key
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

service omega start

CURRENT_IP=$(hostname -I | awk '{print $1}')

echo "PanelOmega downloaded successfully."
echo "Please visit https://$CURRENT_IP:8443 to continue installation of the panel."
