[Unit]
Description=PowerDNS Authoritative Server %i
Documentation=man:pdns_server(1) man:pdns_control(1)
Documentation=https://doc.powerdns.com
Wants=network-online.target
After=network-online.target time-sync.target
Conflicts=named.service

[Service]
ExecStart=/usr/sbin/pdns_server --config-name=%i --guardian=no --daemon=no --disable-syslog --log-timestamp=no --write-pid=no
SyslogIdentifier=pdns_server-%i
User=named
Group=named
Type=notify
Restart=on-failure
RestartSec=1
StartLimitInterval=0
RuntimeDirectory=pdns-%i

# Sandboxing
CapabilityBoundingSet=CAP_NET_BIND_SERVICE CAP_CHOWN
AmbientCapabilities=CAP_NET_BIND_SERVICE CAP_CHOWN
LockPersonality=true
NoNewPrivileges=true
PrivateDevices=true
PrivateTmp=true
# Setting PrivateUsers=true prevents us from opening our sockets
ProtectClock=true
ProtectControlGroups=true
ProtectHome=true
ProtectHostname=true
ProtectKernelLogs=true
ProtectKernelModules=true
ProtectKernelTunables=true
# ProtectSystem=full will disallow write access to /etc and /usr, possibly
# not being able to write slaved-zones into sqlite3 or zonefiles.
ProtectSystem=full
RestrictAddressFamilies=AF_UNIX AF_INET AF_INET6
RestrictNamespaces=true
RestrictRealtime=true
RestrictSUIDSGID=true
SystemCallArchitectures=native
SystemCallFilter=~ @clock @debug @module @mount @raw-io @reboot @swap @cpu-emulation @obsolete
ProtectProc=invisible
PrivateIPC=true
RemoveIPC=true
DevicePolicy=closed
# Not enabled by default because it does not play well with LuaJIT
# MemoryDenyWriteExecute=true

[Install]
WantedBy=multi-user.target
