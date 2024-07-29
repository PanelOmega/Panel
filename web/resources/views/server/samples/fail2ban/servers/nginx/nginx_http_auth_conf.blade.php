[nginx - http - auth]
enabled  = @if(isset($enabled) && $enabled === 'true') {{ $enabled }} @else false @endif
port     = @if(isset($ports)) {{ implode(',', $ports) }} @else http,https @endif
filter   = @if(isset($filter)) {{ $filter }} @else nginx-http-auth @endif
logpath = /@if(isset($logpath)) {{ $logpath }} @else /var/log/vsftpd.log @endif
maxretry = @if(isset($maxretry) && $maxretry > 0) {{ $maxretry }} @else 4 @endif
findtime = @if(isset($findtime) && $findtime > 0) {{ $findtime }} @else 1800 @endif
bantime  = @if(isset($bantime) && $bantime > 0) {{ $bantime }} @else 7200 @endif
action   = @if(isset($action)) {{ $action }} @else iptables[name=HTTP, port=http, protocol=tcp] @endif
