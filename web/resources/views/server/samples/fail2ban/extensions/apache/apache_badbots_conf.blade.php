[apache - badbots]
# Ban hosts which agent identifies spammer robots crawling the web
# for email addresses. The mail outputs are buffered.
port = http,https
logpath = %(apache_access_log)s
bantime = 48h
maxretry = 1
