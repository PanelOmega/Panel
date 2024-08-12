rm -rf httpd-2.4.62-3.el9.x86_64.rpm
rm -rf httpd-core-2.4.62-3.el9.x86_64.rpm
rm -rf httpd-tools-2.4.62-3.el9.x86_64.rpm
rm -rf httpd-filesystem-2.4.62-3.el9.noarch.rpm

wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-2.4.62-3.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-core-2.4.62-3.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/x86_64/httpd-tools-2.4.62-3.el9.x86_64.rpm
wget https://github.com/PanelOmega/Dist/raw/main/compilators/almalinux/my-apache/rpms/noarch/httpd-filesystem-2.4.62-3.el9.noarch.rpm

dnf install -y  httpd-filesystem-2.4.62-3.el9.noarch.rpm httpd-tools-2.4.62-3.el9.x86_64.rpm httpd-core-2.4.62-3.el9.x86_64.rpm httpd-2.4.62-3.el9.x86_64.rpm


chown root:nobody /usr/sbin/suexec
chmod 6750 /usr/sbin/suexec

touch /etc/httpd/logs/suexec_log
chown root:nobody /etc/httpd/logs/suexec_log
chmod 644 /etc/httpd/logs/suexec_log


systemctl enable httpd
systemctl start httpd

