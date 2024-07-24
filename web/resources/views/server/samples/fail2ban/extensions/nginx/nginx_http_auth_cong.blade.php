[nginx - http - auth]

enabled = true
port     = http,https
filter   = nginx-http-auth
logpath  = %(nginx_error_log)s
