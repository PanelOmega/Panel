wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-core-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-tools-2.4.62-2.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/noarch/httpd-filesystem-2.4.62-2.el9.noarch.rpm


dnf install -y httpd-2.4.62-2.el9.x86_64.rpm httpd-core-2.4.62-2.el9.x86_64.rpm httpd-tools-2.4.62-2.el9.x86_64.rpm httpd-filesystem-2.4.62-2.el9.noarch.rpm
