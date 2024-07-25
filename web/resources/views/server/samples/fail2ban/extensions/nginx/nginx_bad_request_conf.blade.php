[nginx - bad - request]
enabled  = true
port     = http,https
filter   = nginx-bad-request
logpath = /var/log/fail2ban.log
maxretry = 3
findtime = 600
bantime  = 3600
action   = iptables[name=HTTP, port=http, protocol=tcp]
