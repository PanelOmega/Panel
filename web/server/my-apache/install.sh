rm -rf httpd-2.4.62-2.el9.x86_64.rpm
rm -rf httpd-core-2.4.62-2.el9.x86_64.rpm
rm -rf httpd-tools-2.4.62-2.el9.x86_64.rpm
rm -rf httpd-filesystem-2.4.62-2.el9.noarch.rpm

wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-core-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-tools-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/noarch/httpd-filesystem-2.4.62-2.el9.noarch.rpm

dnf install -y httpd-filesystem-2.4.62-2.el9.noarch.rpm
dnf install -y httpd-core-2.4.62-2.el9.x86_64.rpm
dnf install -y httpd-tools-2.4.62-2.el9.x86_64.rpm
dnf install -y httpd-2.4.62-2.el9.x86_64.rpm

systemctl enable httpd
systemctl start httpd
