[vsftpd]
enabled = true
port = ftp,ftp-data,ftps,ftps-data
logpath = /var/log/vsftpd.log
findtime = 1800
bantime = 7200
maxretry = 4
banaction = iptables-multiport
