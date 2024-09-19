yum install my-apache -y

chown root:nobody /usr/sbin/suexec
chmod 6750 /usr/sbin/suexec

touch /etc/my-apache/logs/suexec_log
chown root:nobody /etc/my-apache/logs/suexec_log
chmod 644 /etc/my-apache/logs/suexec_log


systemctl enable httpd
systemctl start httpd

