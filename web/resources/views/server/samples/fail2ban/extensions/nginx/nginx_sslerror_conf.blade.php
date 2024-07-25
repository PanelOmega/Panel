[nginx - sslerror]

enabled = true
port    = http,https
filter  = nginx-sslerror
logpath = %(nginx_error_log)s
