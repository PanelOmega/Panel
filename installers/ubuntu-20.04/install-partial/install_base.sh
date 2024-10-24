GIT_BRANCH="stable"
if [ -n "$1" ]; then
    GIT_BRANCH=$1
fi

INSTALL_DIR="/omega/install"

apt-get update && apt-get install ca-certificates -y

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

wget https://raw.githubusercontent.com/PanelOmega/Panel/$GIT_BRANCH/installers/ubuntu-20.04/greeting.sh
mv greeting.sh /etc/profile.d/omega-greeting.sh

# Install OMEGA PHP
wget https://github.com/PanelOmega/Dist/raw/main/compilators/debian/php/dist/omega-php-8.2.0-ubuntu-20.04.deb
dpkg -i omega-php-8.2.0-ubuntu-20.04.deb

# Install OMEGA NGINX
wget https://github.com/PanelOmega/Dist/raw/main/compilators/debian/nginx/dist/omega-nginx-1.24.0-ubuntu-20.04.deb
dpkg -i omega-nginx-1.24.0-ubuntu-20.04.deb

OMEGA_PHP=/usr/local/omega/php/bin/php
ln -s $OMEGA_PHP /usr/bin/omega-php
