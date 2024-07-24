[nginx-4xx]

enabled = true
port     = http,https
filter   = nginx-4xx
logpath  = %(nginx_error_log)s
