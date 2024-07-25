[apache]
enabled  = true
port     = http,https
action   = iptables[name=HTTP, port=http, protocol=tcp]
logpath = /var/log/fail2ban.log
filter   = apache-auth
maxretry = 3
findtime = 600
bantime  = 3600
