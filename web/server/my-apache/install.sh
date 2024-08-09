CURRENT_DIR=$(pwd)
rm -rf $CURRENT_DIR/httpd
cd $CURRENT_DIR/httpd
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-core-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-tools-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/noarch/httpd-filesystem-2.4.62-2.el9.noarch.rpm

cd $CURRENT_DIR

dnf install -y $CURRENT_DIR/httpd/httpd-2.4.62-2.el9.x86_64.rpm $CURRENT_DIR/httpd/httpd-core-2.4.62-2.el9.x86_64.rpm $CURRENT_DIR/httpd/httpd-tools-2.4.62-2.el9.x86_64.rpm $CURRENT_DIR/httpd/httpd-filesystem-2.4.62-2.el9.noarch.rpm

systemctl enable httpd
systemctl start httpd
