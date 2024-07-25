[wordpress - hard]
enabled = true
filter = wordpress-hard
action = iptables[name=HTTP, port=http, protocol=tcp]
port = http,https
logpath = /var/log/vsftpd.log
findtime = 1800
bantime = 7200
maxretry = 4
