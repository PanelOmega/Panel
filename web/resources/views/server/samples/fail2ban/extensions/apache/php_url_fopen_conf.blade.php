# Ban attackers that try to use PHP's URL-fopen() functionality
# through GET/POST variables. - Experimental, with more than a year
# of usage in production environments.


[php - url - fopen]

enabled = true
port    = http,https
filter  = php-url-fopen
logpath = /var/log/apache*/*access.log
