[wordpress - auth]
enabled = true
filter = wordpress-auth
action = iptables[name=HTTP, port=http, protocol=tcp]
logpath = /var/log/vsftpd.log
findtime = 1800
bantime = 7200
maxretry = 4
