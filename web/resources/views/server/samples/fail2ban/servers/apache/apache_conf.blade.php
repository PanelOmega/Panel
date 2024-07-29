[apache]
enabled  = @if(isset($enabled) && $enabled === true) true @else false @endif
port     = @if(isset($port)) {{ $port }} @else http,https @endif
action   = @if(isset($action)) {{ $action }} @else iptables[name=HTTP, port=http, protocol=tcp] @endif
logpath = @if(isset($logpath)) {{ $logpath }} @else /var/log/fail2ban.log @endif
filter   = @if(isset($filter)) {{ $filter }} apache-auth @else @endif
findtime = @if(isset($findtime) && $findtime > 0) {{ $findtime }} @else 1800 @endif
bantime  = @if(isset($bantime) && $bantime > 0) {{ $bantime }} @else 7200 @endif
maxretry = @if(isset($maxretry) && $maxretry > 0) {{ $maxretry }} @else 4 @endif
