[vsftpd]

enabled = true
# or overwrite it in jails.local to be
# logpath = %(syslog_authpriv)s
# if you want to rely on PAM failed login attempts
# vsftpd's failregex should match both of those formats
port = ftp,ftp-data,ftps,ftps-data
logpath = %(vsftpd_log)s
findtime=1800
bantime = 7200
maxretry = 4
