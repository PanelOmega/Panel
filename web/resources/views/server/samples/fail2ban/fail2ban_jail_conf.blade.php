#
# WARNING: heavily refactored in 0.9.0 release.  Please review and
#          customize settings for your setup.
#
# Changes:  in most of the cases you should not modify this
#           file, but provide customizations in jail.local file,
#           or separate .conf files under jail.d/ directory, e.g.:
#
# HOW TO ACTIVATE JAILS:
#
# YOU SHOULD NOT MODIFY THIS FILE.
#
# It will probably be overwritten or improved in a distribution update.
#
# Provide customizations in a jail.local file or a jail.d/customisation.local.
# For example to change the default bantime for all jails and to enable the
# ssh-iptables jail the following (uncommented) would appear in the .local file.
# See man 5 jail.conf for details.
#
[DEFAULT]
#
# See jail.conf(5) man page for more information


# Comments: use '#' for comment lines and ';' (following a space) for inline comments


[INCLUDES]

#before = paths-distro.conf
before = paths - fedora . conf

# The DEFAULT allows a global definition of the options. They can be overridden
# in each jail afterwards.

[DEFAULT]

#
# MISCELLANEOUS OPTIONS
#

# "bantime.increment" allows to use database for searching of previously banned ip's to increase a
# default ban time using special formula, default it is banTime * 1, 2, 4, 8, 16, 32...
#bantime.increment = true

# "bantime.rndtime" is the max number of seconds using for mixing with random time
# to prevent "clever" botnets calculate exact time IP can be unbanned again:
#bantime.rndtime =

# "bantime.maxtime" is the max number of seconds using the ban time can reach (doesn't grow further)
#bantime.maxtime =

# "bantime.factor" is a coefficient to calculate exponent growing of the formula or common multiplier,
# default value of factor is 1 and with default value of formula, the ban time
# grows by 1, 2, 4, 8, 16 ...
#bantime.factor = 1

# "bantime.formula" used by default to calculate next value of ban time, default value below,
# the same ban time growing will be reached by multipliers 1, 2, 4, 8, 16, 32...
#bantime.formula = ban.Time * (1<<(ban.Count if ban.Count<20 else 20)) * banFactor
#
# more aggressive example of formula has the same values only for factor "2.0 / 2.885385" :
#bantime.formula = ban.Time * math.exp(float(ban.Count+1)*banFactor)/math.exp(1*banFactor)

# "bantime.multipliers" used to calculate next value of ban time instead of formula, corresponding
# previously ban count and given "bantime.factor" (for multipliers default is 1);
# following example grows ban time by 1, 2, 4, 8, 16 ... and if last ban count greater as multipliers count,
# always used last multiplier (64 in example), for factor '1' and original ban time 600 - 10.6 hours
#bantime.multipliers = 1 2 4 8 16 32 64
# following example can be used for small initial ban time (bantime=60) - it grows more aggressive at begin,
# for bantime=60 the multipliers are minutes and equal: 1 min, 5 min, 30 min, 1 hour, 5 hour, 12 hour, 1 day, 2 day
#bantime.multipliers = 1 5 30 60 300 720 1440 2880

# "bantime.overalljails" (if true) specifies the search of IP in the database will be executed
# cross over all jails, if false (default), only current jail of the ban IP will be searched
#bantime.overalljails = false

# --------------------

# "ignoreself" specifies whether the local resp. own IP addresses should be ignored
# (default is true). Fail2ban will not ban a host which matches such addresses.
#ignoreself = true

# "ignoreip" can be a list of IP addresses, CIDR masks or DNS hosts. Fail2ban
# will not ban a host which matches an address in this list. Several addresses
# can be defined using space (and/or comma) separator.

ignoreip = @if(isset($whitelistedIps)) {{ implode(',', $whitelistedIps) }} @endif

# External command that will take an tagged arguments to ignore, e.g.
# and return true if the IP is to be ignored. False otherwise.
#
# ignorecommand = /path/to/command
ignorecommand = @if(isset($setting['general']['ignorecommand']) && $settings['general']['ignorecommand'] !== '') {{ $settings['general']['ignorecommand'] }} @else @endif

# "bantime" is the number of seconds that a host is banned.
bantime = @if(isset($settings['general']['bantime']) && $settings['general']['bantime'] !== '' && isset($settings['general']['fail2ban']['config']['general']['bantime_unit'])) {{ $settings['general']['bantime'] }}{{ $settings['general']['fail2ban']['config']['general']['bantime_unit'] }} @else 1h @endif


# A host is banned if it has generated "maxretry" during the last "findtime"
# seconds.

findtime = @if(isset($settings['general']['findtime']) && $settings['general']['findtime'] > 0 && isset($settings['general']['fail2ban']['config']['general']['findtime_unit'])) {{ $settings['general']['findtime'] }}{{ $settings['general']['fail2ban']['config']['general']['findtime_unit'] }} @else 10m @endif

# "maxretry" is the number of failures before a host get banned.
maxretry = @if(isset($settings['general']['maxretry']) && $settings['general']['maxretry'] > 0) {{ $settings['general']['maxretry'] }} @else 5 @endif

# "maxmatches" is the number of matches stored in ticket (resolvable via tag
#< matches> in actions).
#maxmatches = %(maxretry)s

# "backend" specifies the backend used to get files modification.
# Available options are "pyinotify", "gamin", "polling", "systemd" and "auto".
# This option can be overridden in each jail as well.
#
# pyinotify: requires pyinotify (a file alteration monitor) to be installed.
# If pyinotify is not installed, Fail2ban will use auto.
# gamin: requires Gamin (a file alteration monitor) to be installed.
# If Gamin is not installed, Fail2ban will use auto.
# polling: uses a polling algorithm which does not require external libraries.
# systemd: uses systemd python library to access the systemd journal.
# Specifying "logpath" is not valid for this backend.
# See "journalmatch" in the jails associated filter config
# auto: will try to use the following backends, in order:
# pyinotify, gamin, polling.
#
# Note: if systemd backend is chosen as the default but you enable a jail
# for which logs are present only in its own log files, specify some other
# backend for that jail (e.g. polling) and provide empty value for
# journalmatch. See https://github.com/fail2ban/fail2ban/issues/959#issuecomment-74901200
backend = @if(isset($settings['general']['backend']) && ($settings['general']['backend'] !== 'auto' && $settings['general']['backend'] !== '')) {{ $settings['general']['backend'] }} @else auto @endif

# "usedns" specifies if jails should trust hostnames in logs,
# warn when DNS lookups are performed, or ignore all hostnames in logs
#
# yes: if a hostname is encountered, a DNS lookup will be performed.
# warn: if a hostname is encountered, a DNS lookup will be performed,
# but it will be logged as a warning.
# no: if a hostname is encountered, will not be used for banning,
# but it will be logged as info.
# raw: use raw value (no hostname), allow use it for no-host filters/actions (example user)
usedns = @if(isset($settings['general']['usedns']) && ($settings['general']['usedns'] !== 'warn' && $settings['general']['usedns'] !== '')) {{ $settings['general']['usedns'] }} @else warn @endif

# "logencoding" specifies the encoding of the log files handled by the jail
# This is used to decode the lines from the log file.
# Typical examples: "ascii", "utf-8"
#
# auto: will use the system locale setting

logencoding = @if(isset($settings['general']['logencoding']) && ($settings['general']['logencoding'] !== 'auto' && $settings['general']['logencoding'] !== '')) {{ $settings['general']['logencoding'] }} @else auto @endif

# "enabled" enables the jails.
# By default all jails are disabled, and it should stay this way.
# Enable only relevant to your setup jails in your .local or jail.d/*.conf
#
# true: jail will be enabled and log files will get monitored for changes
# false: jail is not enabled
enabled = @if(isset($settings['general']['enabled']) && $settings['general']['enabled'] === true) true @else false @endif


# "mode" defines the mode of the filter (see corresponding filter implementation for more info).
#mode = normal

# "filter" defines the filter to use by the jail.
# By default jails have names matching their filter name
#
#filter = %(__name__)s[mode =%(mode)s]


#
# ACTIONS
#

# Some options used for actions

# Destination email address used solely for the interpolations in
# jail.{conf,local,d/*} configuration files.
#destemail = @if(isset($settings['action']['destemail'])) {{ $settings['action']['destemail'] }} @else @endif


# Sender email address used solely for some actions
#sender = root@

#sender = @if(isset($settings['action']['sender'])) {{ $settings['action']['sender'] }} @else @endif


# E-mail action. Since 0.8.1 Fail2Ban uses sendmail MTA for the
# mailing. Change mta configuration parameter to mail if you want to
# revert to conventional 'mail'.

#mta = @if(isset($settings['action']['mta']) && $settings['action']['mta'] !== 'sendmail') {{ $settings['action']['mta'] }} @else sendmail @endif

# Default protocol
#protocol = @if(isset($settings['action']['protocol']) && $settings['action']['protocol'] !== 'tcp') {{ $settings['action']['protocol'] }} @else tcp @endif

# Specify chain where jumps would need to be added in ban-actions expecting parameter chain
#chain = <known/chain>

# Ports to be banned
# Usually should be overridden in a particular jail
port = @if(isset($settings['action']['port']) && $settings['action']['port'] !== '0-65535') {{ $settings['action']['port'] }} @else 0-65535 @endif

# Format of user-agent https://tools.ietf.org/html/rfc7231#section-5.5.3
fail2ban_agent = Fail2Ban /%(fail2ban_version)s

#
# Action shortcuts. To be used to define action parameter

# Default banning action (e.g. iptables, iptables-new,
# iptables-multiport, shorewall, etc) It is used to define
# action_* variables. Can be overridden globally or per
# section within jail.local file

#banaction = @if(isset($settings['action']['banaction']) && $settings['action']['banaction'] !== 'iptables-multiport') {{ $settings['action']['banaction'] }} @else iptables-multiport @endif

#banaction_allports = iptables - allports

# The simplest action to take: ban only
#action_ = %(banaction)s[port = "%(port)s", protocol = "%(protocol)s", chain = "%(chain)s"]

# ban & send an e-mail with whois report to the destemail.
#action_mw = %(action_)s
#% (mta)s - whois[sender = "%(sender)s", dest = "%(destemail)s", protocol = "%(protocol)s", chain =
#"%(chain)s"]

# ban & send an e-mail with whois report and relevant log lines
# to the destemail.
#action_mwl = %(action_)s
#% (mta)s - whois - lines[sender = "%(sender)s", dest = "%(destemail)s", logpath = "%(logpath)s", chain =
#"%(chain)s"]

# See the IMPORTANT note in action.d/xarf-login-attack for when to use this action
#
# ban & send a xarf e-mail to abuse contact of IP address and include relevant log lines
# to the destemail.
#action_xarf = %(action_)s
#xarf - login - attack[service =%(__name__)s, sender = "%(sender)s", logpath = "%(logpath)s", port =
#"%(port)s"]

# ban & send a notification to one or more of the 50+ services supported by Apprise.
# See https://github.com/caronc/apprise/wiki for details on what is supported.
#
# You may optionally over-ride the default configuration line (containing the Apprise URLs)
# by using 'apprise[config="/alternate/path/to/apprise.cfg"]' otherwise
# /etc/fail2ban/apprise.conf is sourced for your supported notification configuration.
# action = %(action_)s
# apprise

# ban IP on CloudFlare & send an e-mail with whois report and relevant log lines
# to the destemail.
№action_cf_mwl = cloudflare[cfuser = "%(cfemail)s", cftoken = "%(cfapikey)s"]
#%(mta)s - whois - lines[sender = "%(sender)s", dest = "%(destemail)s", logpath = "%(logpath)s", chain =
#"%(chain)s"]

# Report block via blocklist.de fail2ban reporting service API
#
# See the IMPORTANT note in action.d/blocklist_de.conf for when to use this action.
# Specify expected parameters in file action.d/blocklist_de.local or if the interpolation
# `action_blocklist_de` used for the action, set value of `blocklist_de_apikey`
# in your `jail.local` globally (section [DEFAULT]) or per specific jail section (resp. in
# corresponding jail.d/my-jail.local file).
#
#action_blocklist_de = blocklist_de[email = "%(sender)s", service = "%(__name__)s",
apikey = "%(blocklist_de_apikey)s", agent = "%(fail2ban_agent)s"]
# Report ban via abuseipdb.com.
#
# See action.d/abuseipdb.conf for usage example and details.
#
#action_abuseipdb =  abuseipdb

# Choose default action. To change, just override value of 'action' with the
# interpolation to the chosen action shortcut (e.g. action_mw, action_mwl, etc) in jail.local
# globally (section [DEFAULT]) or per specific section
#action = %(action_)s


#
# JAILS
#

#
# SSH servers
#


#
# HTTP servers
#

[openhab-auth]
filter = openhab
banaction = %(banaction_allports)s
logpath = /var/log/fail2ban.log

# To use more aggressive http-auth modes set filter parameter "mode" in jail.local:
# normal (default), aggressive (combines all), auth or fallback
# See "tests/files/logs/nginx-http-auth" or "filter.d/nginx-http-auth.conf" for usage example and
#details .
[nginx-http-auth]
# mode = normal
port    = http,https
logpath = /var/log/fail2ban.log


# To use 'nginx-limit-req' jail you should have `ngx_http_limit_req_module`
# and define `limit_req` and `limit_req_zone` as described in nginx documentation
# http://nginx.org/en/docs/http/ngx_http_limit_req_module.html
# or for example see in 'config/filter.d/nginx-limit-req.conf'
[nginx-limit-req]
port    = http,https
logpath = /var/log/fail2ban.log

[nginx-botsearch]
port     = http,https
logpath  = /var/log/fail2ban.log

[nginx-bad-request]
port    = http,https
logpath = /var/log/fail2ban.log


[lighttpd-auth]
# Same as above for Apache's mod_auth
# It catches wrong authentifications
port    = http,https
logpath = /var/log/fail2ban.log


#
# Webmail and groupware servers
#

[roundcube-auth]
port     = http,https
logpath  = /var/log/fail2ban.log
# Use following line in your jail.local if roundcube logs to journal.
#backend = %(syslog_backend)s

[openwebmail]

port     = http,https
logpath  = /var/log/fail2ban.log

[horde]
port     = http,https
logpath  = /var/log/fail2ban.log


[groupoffice]
port     = http,https
logpath  = /var/log/fail2ban.log


[sogo-auth]
# Monitor SOGo groupware server
# without proxy this would be:
# port    = 20000
port     = http,https
logpath  = /var/log/fail2ban.log


[tine20]
logpath  = /var/log/fail2ban.log
port     = http,https

#
# Web Applications
#
#

[guacamole]
port     = http,https
logpath  = /var/log/fail2ban.log


[monit]
#Ban clients brute-forcing the monit gui login
port = 2812
logpath  = /var/log/fail2ban.log
#
# HTTP Proxy servers
#
#


[squid]
port    =  3128,8080
logpath = /var/log/fail2ban.log


[3proxy]
port    = 3128
logpath = /var/log/fail2ban.log


#
# FTP servers
#


#
# Mail servers
#

# ASSP SMTP Proxy Jail
[assp]
port     = smtp,465,submission
logpath  = /var/log/fail2ban.log


[qmail-rbl]
filter  = qmail
port    = smtp,465,submission
logpath = /var/log/fail2ban.log


[exim]
# see filter.d/exim.conf for further modes supported from filter:
#mode = normal
port   = smtp,465,submission
logpath = /var/log/fail2ban.log


[exim-spam]
port   = smtp,465,submission
logpath = /var/log/fail2ban.log


[kerio]
port    = imap,smtp,imaps,465
logpath = /var/log/fail2ban.log


#
# Mail servers authenticators: might be used for smtp,ftp,imap servers, so
# all relevant ports get banned
#


[squirrelmail]
port = smtp,465,submission,imap,imap2,imaps,pop3,pop3s,http,https,socks
logpath = /var/log/fail2ban.log

#
#
# DNS servers
#


[named-refused]
port     = domain,953
logpath  = /var/log/fail2ban.log


[nsd]
port     = 53
action_  = %(default/action_)s[name=%(__name__)s-tcp, protocol="tcp"]
%(default/action_)s[name=%(__name__)s-udp, protocol="udp"]
logpath = /var/log/fail2ban.log


# !!! WARNING !!!
# Since UDP is connection-less protocol, spoofing of IP and imitation
# of illegal actions is way too simple. Thus enabling of this filter
# might provide an easy way for implementing a DoS against a chosen
# victim. See
# http://nion.modprobe.de/blog/archives/690-fail2ban-+-dns-fail.html
# Please DO NOT USE this jail unless you know what you are doing.
#
# IMPORTANT: see filter.d/named-refused for instructions to enable logging
# This jail blocks UDP traffic for DNS requests.

#
# Miscellaneous
#

[asterisk]
port     = 5060,5061
action_  = %(default/action_)s[name=%(__name__)s-tcp, protocol="tcp"]
%(default/action_)s[name=%(__name__)s-udp, protocol="udp"]
logpath  = /var/log/fail2ban.log
maxretry = 10


[freeswitch]
port     = 5060,5061
action_  = %(default/action_)s[name=%(__name__)s-tcp, protocol="tcp"]
%(default/action_)s[name=%(__name__)s-udp, protocol="udp"]
logpath  = /var/log/fail2ban.log
maxretry = 10


# enable adminlog; it will log to a file inside znc's directory by default.
[znc-adminlog]
port     = 6667
logpath  = /var/log/fail2ban.log

# To log wrong MySQL access attempts add to /etc/my.cnf in [mysqld] or
# equivalent section:
# log-warnings = 2
#
# for syslog (daemon facility)
# [mysqld_safe]
# syslog
#
# for own logfile
# [mysqld]
# log-error=/var/log/mysqld.log
[mysqld-auth]
port     = 3306
logpath  = /var/log/fail2ban.log
backend  = %(mysql_backend)s


[mssql-auth]
# Default configuration for Microsoft SQL Server for Linux
# See the 'mssql-conf' manpage how to change logpath or port
logpath = /var/log/fail2ban.log
port = 1433
filter = mssql-auth


# Log wrong MongoDB auth (for details see filter 'filter.d/mongodb-auth.conf')
[mongodb-auth]
# change port when running with "--shardsvr" or "--configsvr" runtime operation
port     = 27017
logpath  = /var/log/fail2ban.log



# Jail for more extended banning of persistent abusers
# !!! WARNINGS !!!
# 1. Make sure that your loglevel specified in fail2ban.conf/.local
# is not at DEBUG level -- which might then cause fail2ban to fall into
# an infinite loop constantly feeding itself with non-informative lines
# 2. Increase dbpurgeage defined in fail2ban.conf to e.g. 648000 (7.5 days)
# to maintain entries for failed logins for sufficient amount of time


# stunnel - need to set port for this
[stunnel]
logpath = /var/log/fail2ban.log


[ejabberd-auth]
port    = 5222
logpath = /var/log/fail2ban.log



[counter-strike]

logpath = /var/log/fail2ban.log
tcpport = 27030,27031,27032,27033,27034,27035,27036,27037,27038,27039
udpport = 1200,27000,27001,27002,27003,27004,27005,27006,27007,27008,27009,27010,27011,27012,27013,27014,27015
action_  = %(default/action_)s[name=%(__name__)s-tcp, port="%(tcpport)s", protocol="tcp"]



[softethervpn]
port     = 500,4500
protocol = udp
logpath  = /var/log/fail2ban.log


[gitlab]
port    = http,https
logpath = /var/log/fail2ban.log


[grafana]
port    = http,https
logpath = /var/log/fail2ban.log


[bitwarden]
port    = http,https
logpath = /var/log/fail2ban.log


[centreon]
port    = http,https
logpath = /var/log/fail2ban.log


[oracleims]
# see "oracleims" filter file for configuration requirement for Oracle IMS v6 and above
logpath = /var/log/fail2ban.log
banaction = %(banaction_allports)s


[portsentry]
logpath  = /var/log/fail2ban.log
maxretry = 1


[directadmin]
logpath = /var/log/fail2ban.log
port = 2222


[murmur]
# AKA mumble-server
port     = 64738
action_  = %(default/action_)s[name=%(__name__)s-tcp, protocol="tcp"]
%(default/action_)s[name=%(__name__)s-udp, protocol="udp"]
logpath  = /var/log/fail2ban.log


[screensharingd]
# For Mac OS Screen Sharing Service (VNC)
logpath  = /var/log/fail2ban.log
logencoding = utf-8


[haproxy-http-auth]
# HAProxy by default doesn't log to file you'll need to set it up to forward
# logs to a syslog server which would then write them to disk.
# See "haproxy-http-auth" filter for a brief cautionary note when setting
# maxretry and findtime.
logpath  = /var/log/fail2ban.log


[slapd]
port    = ldap,ldaps
logpath = /var/log/fail2ban.log


[domino-smtp]
port    = smtp,ssmtp
logpath = /var/log/fail2ban.log


[traefik-auth]
# to use 'traefik-auth' filter you have to configure your Traefik instance,
# see `filter.d/traefik-auth.conf` for details and service example.
port    = http,https
logpath = /var/log/fail2ban.log


[monitorix]
port	= 8080
logpath = /var/log/fail2ban.log



[sshd]
# To use more aggressive sshd modes set filter parameter "mode" in jail.local:
# normal (default), ddos, extra or aggressive (combines all).
# See "tests/files/logs/sshd" or "filter.d/sshd.conf" for usage example and details.
@if(isset($settings['jails']['sshd']['enabled']) && $settings['jails']['sshd']['enabled'] === true) enabled = true @endif {{ PHP_EOL }}
port = ssh @if(isset($settings['jails']['sshd']['port']) && !strpos($settings['jails']['sshd']['port'], 'ssh')) {{ $settings['jails']['sshd']['port'] }} @endif {{ PHP_EOL }}
filter = @if(isset($settings['jails']['sshd']['filter'])) {{ $settings['jails']['sshd']['filter'] }} @else sshd @endif {{ PHP_EOL }}
findtime = @if(isset($settings['jails']['sshd']['findtime']) && isset($settings['jails']['sshd']['fail2ban']['config']['jails']['sshd']['findtime_unit']) && $settings['jails']['sshd']['findtime'] > 0) {{ $settings['jails']['sshd']['findtime'] }}{{ $settings['jails']['sshd']['fail2ban']['config']['jails']['sshd']['findtime_unit'] }} @else 1800m @endif {{ PHP_EOL }}
bantime = @if(isset($settings['jails']['sshd']['bantime']) && isset($settings['jails']['sshd']['fail2ban']['config']['jails']['sshd']['bantime_unit']) && $settings['jails']['sshd']['bantime'] > 0) {{ $settings['jails']['sshd']['bantime'] }}{{ $settings['jails']['sshd']['fail2ban']['config']['jails']['sshd']['bantime_unit'] }} @else 7200m @endif {{ PHP_EOL }}
maxretry = @if(isset($settings['jails']['sshd']['maxretry']) && $settings['jails']['sshd']['maxretry'] > 0) {{ $settings['jails']['sshd']['maxretry'] }} @else 4 @endif {{ PHP_EOL }}
banaction = @if(isset($settings['jails']['sshd']['banaction'])) {{ $settings['jails']['sshd']['banaction'] }} @else iptables-multiport @endif {{ PHP_EOL }}
logpath = @if(isset($settings['jails']['sshd']['logpath'])) {{ $settings['jails']['sshd']['logpath'] }} @else /var/log/fail2ban.log @endif


[apache]
@if(isset($settings['jails']['apache']['enabled']) && $settings['jails']['apache']['enabled'] === true) enabled = true @endif {{ PHP_EOL }}
port = http,https,@if(isset($settings['jails']['apache']['port']) && (!strpos($settings['jails']['apache']['port'], 'http') && !strpos($settings['jails']['apache']['port'], 'https'))) {{ $settings['jails']['apache']['port'] }} @endif {{ PHP_EOL }}
filter = @if(isset($settings['jails']['apache']['filter'])) {{ $settings['jails']['apache']['filter'] }} @else apache-common @endif {{ PHP_EOL }}
findtime = @if(isset($settings['jails']['apache']['findtime']) && isset($settings['jails']['apache']['fail2ban']['config']['jails']['apache']['findtime_unit']) && $settings['jails']['apache']['findtime'] > 0) {{ $settings['jails']['apache']['findtime'] }}{{ $settings['jails']['apache']['fail2ban']['config']['jails']['apache']['findtime_unit'] }} @else 1800m @endif {{ PHP_EOL }}
bantime = @if(isset($settings['jails']['apache']['bantime']) && isset($settings['jails']['apache']['fail2ban']['config']['jails']['apache']['bantime_unit']) && $settings['jails']['apache']['bantime'] > 0) {{ $settings['jails']['apache']['bantime'] }}{{ $settings['jails']['apache']['fail2ban']['config']['jails']['apache']['bantime_unit'] }} @else 7200m @endif {{ PHP_EOL }}
maxretry = @if(isset($settings['jails']['apache']['maxretry']) && $settings['jails']['apache']['maxretry'] > 0) {{ $settings['jails']['apache']['maxretry'] }} @else 4 @endif {{ PHP_EOL }}
banaction = @if(isset($settings['jails']['apache']['banaction'])) {{ $settings['jails']['apache']['banaction'] }} @else iptables @endif {{ PHP_EOL }}
logpath = @if(isset($settings['jails']['apache']['logpath'])) {{ $settings['jails']['apache']['logpath'] }} @else /var/log/fail2ban.log @endif


[vsftpd]
@if(isset($settings['jails']['vsftpd']['enabled']) && $settings['jails']['vsftpd']['enabled'] === true) enabled = true @endif {{ PHP_EOL }}
port = ftp,ftp-data,ftps,ftps-data @if(isset($settings['jails']['vsftpd']['port'])) {{$settings['jails']['vsftpd']['port'] }} @endif {{ PHP_EOL }}
filter = @if(isset($settings['jails']['vsftpd']['filter'])) {{ $settings['jails']['vsftpd']['filter'] }} @else vsftpd @endif {{ PHP_EOL }}
findtime = @if(isset($settings['jails']['vsftpd']['findtime']) && isset($settings['jails']['vsftpd']['fail2ban']['config']['jails']['vsftpd']['findtime_unit']) && $settings['jails']['vsftpd']['findtime'] > 0) {{ $settings['jails']['vsftpd']['findtime'] }}{{ $settings['jails']['vsftpd']['fail2ban']['config']['jails']['vsftpd']['findtime_unit'] }} @else 1800m @endif {{ PHP_EOL }}
bantime= @if(isset($settings['jails']['vsftpd']['bantime']) && isset($settings['jails']['vsftpd']['fail2ban']['config']['jails']['vsftpd']['bantime_unit']) && $settings['jails']['vsftpd']['bantime'] > 0) {{ $settings['jails']['vsftpd']['bantime'] }}{{ $settings['jails']['vsftpd']['fail2ban']['config']['jails']['vsftpd']['bantime_unit'] }} @else 7200m @endif {{ PHP_EOL }}
maxretry = @if(isset($settings['jails']['vsftpd']['maxretry']) && $settings['jails']['vsftpd']['maxretry'] > 0) {{ $settings['jails']['vsftpd']['maxretry'] }} @else 4 @endif {{ PHP_EOL }}
banaction = @if(isset($settings['jails']['vsftpd']['banaction'])) {{ $settings['jails']['vsftpd']['banaction'] }} @else iptables-multiport @endif {{ PHP_EOL }}
logpath = @if(isset($settings['jails']['vsftpd']['logpath'])) {{ $settings['jails']['vsftpd']['logpath'] }} @else /var/log/fail2ban.log @endif

