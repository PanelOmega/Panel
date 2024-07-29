[vsftpd]
enabled = @if(isset($enabled) && $enabled === 'true') {{ $enabled }} @else false @endif
port = @if(isset($ports)) {{ implode(',', $ports) }} @else ftp,ftp-data,ftps,ftps-data @endif
logpath = @if(isset($logpath)) {{ $logpath }} @else /var/log/vsftpd.log @endif
findtime = @if(isset($findtime) && $findtime > 0) {{ $findtime }} @else 1800 @endif
bantime = @if(isset($bantime) && $bantime > 0) {{ $bantime }} @else 7200 @endif
maxretry = @if(isset($maxretry) && $maxretry > 0) {{ $maxretry }} @else 4 @endif
banaction = @if(isset($banaction)) {{ $banaction }} @else iptables-multiport @endif
