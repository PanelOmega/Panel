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
