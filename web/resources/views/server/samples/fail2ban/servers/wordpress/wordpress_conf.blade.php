[wordpress]
enabled = true
port = http,https
filter = wordpress
action = iptables-multiport[name=wordpress, port="http,https", protocol=tcp]
logpath = /var/log/vsftpd.log
findtime = 1800
bantime = 7200
maxretry = 4
