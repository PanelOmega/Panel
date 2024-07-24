[nginx - forbidden]

enabled = true
port    = http,https
filter  = nginx-forbidden
logpath = %(nginx_error_log)s
