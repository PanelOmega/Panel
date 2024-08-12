@if($os == \App\Server\Helpers\OS::UBUNTU || $os == \App\Server\Helpers\OS::DEBIAN)
@include('server.samples.configs.ubuntu.apache2-top-conf')
@elseif($os == \App\Server\Helpers\OS::CLOUD_LINUX || $os == \App\Server\Helpers\OS::ALMA_LINUX || $os == \App\Server\Helpers\OS::CENTOS)
    @include('server.samples.configs.almalinux.apache2-top-conf')
@endif

@if(isset($installedPHPVersions))

ScriptAlias /cgi-sys /usr/local/omega/cgi-sys/

@foreach($installedPHPVersions as $phpVersion)
Action {{$phpVersion['action']}}
@endforeach
@endif

@foreach($virtualHosts as $virtualHost)

<VirtualHost *:{{$virtualHost['port']}}>

    @if(!empty($virtualHost['serverAdmin']))

        ServerAdmin {{$virtualHost['serverAdmin']}}

    @endif

    ServerName {{$virtualHost['domain']}}

    @if(!empty($virtualHost['domainAlias']))

        ServerAlias {{$virtualHost['domainAlias']}}

    @endif

    DocumentRoot {{$virtualHost['domainPublic']}}
    SetEnv APP_DOMAIN {{$virtualHost['domain']}}
    SuexecUserGroup {{$virtualHost['user']}} {{$virtualHost['group']}}

    @if(isset($virtualHost['enableRuid2']) && $virtualHost['enableRuid2'] && !empty($virtualHost['user']) && !empty($virtualHost['group']))

        #RDocumentChRoot {{$virtualHost['domainPublic']}}
        #RUidGid {{$virtualHost['user']}} {{$virtualHost['group']}}

    @endif

    @if($virtualHost['enableLogs'])

        LogFormat "%h %l %u %t \"%r\" %>s %b" common

        CustomLog {{$virtualHost['domainRoot']}}/logs/apache2/bytes.log bytes
        CustomLog {{$virtualHost['domainRoot']}}/logs/apache2/access.log common
        ErrorLog {{$virtualHost['domainRoot']}}/logs/apache2/error.log

    @endif

    @if (!empty($virtualHost['proxyPass']))

        ProxyPreserveHost On
        ProxyRequests Off
        ProxyVia On
        ProxyPass / {{$virtualHost['proxyPass']}}
        ProxyPassReverse / {{$virtualHost['proxyPass']}}

    @endif

    <Directory {{$virtualHost['domainPublic']}}>

        Options Indexes FollowSymLinks MultiViews @if($virtualHost['appType'] == 'php') Includes ExecCGI @endif

        AllowOverride All
        Require all granted

        @if($virtualHost['passengerAppRoot'] !== null)

            PassengerAppRoot {{$virtualHost['passengerAppRoot']}}

            PassengerAppType {{$virtualHost['passengerAppType']}}

            @if($virtualHost['passengerStartupFile'] !== null)
                PassengerStartupFile {{$virtualHost['passengerStartupFile']}}
            @endif

        @endif

        @if($virtualHost['appType'] == 'php_proxy_fcgi' && isset($virtualHost['fcgi']))
            <Files *.php>
                SetHandler "proxy:fcgi://{{$virtualHost['fcgi']}}"
            </Files>
        @endif

        @if($virtualHost['appType'] == 'php')

            @php
                $appendOpenBaseDirs = $virtualHost['homeRoot'];
                if (isset($virtualHost['phpAdminValueOpenBaseDirs'])
                        && is_array($virtualHost['phpAdminValueOpenBaseDirs'])
                        && !empty($virtualHost['phpAdminValueOpenBaseDirs'])) {
                    $appendOpenBaseDirs .= ':' . implode(':', $virtualHost['phpAdminValueOpenBaseDirs']);
                }
            @endphp

            php_admin_value open_basedir {{$appendOpenBaseDirs}}

            php_admin_value upload_tmp_dir {{$virtualHost['homeRoot']}}/tmp
            php_admin_value session.save_path {{$virtualHost['homeRoot']}}/tmp
            php_admin_value sys_temp_dir {{$virtualHost['homeRoot']}}/tmp

        @endif

    </Directory>

    @if(!empty($virtualHost['sslCertificateFile']) and !empty($virtualHost['sslCertificateKeyFile']))

        SSLEngine on
        SSLCertificateFile {{$virtualHost['sslCertificateFile']}}
        SSLCertificateKeyFile {{$virtualHost['sslCertificateKeyFile']}}

        @if (!empty($virtualHost['sslCertificateChainFile']))

            SSLCertificateChainFile {{$virtualHost['sslCertificateChainFile']}}

        @endif


        SSLEngine on

        # Intermediate configuration, tweak to your needs
        SSLProtocol             all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
        SSLCipherSuite          ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
        SSLHonorCipherOrder     off
        SSLSessionTickets       off

        SSLOptions +StrictRequire

        # Add vhost name to log entries:
        LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-agent}i\"" vhost_combined
        LogFormat "%v %h %l %u %t \"%r\" %>s %b" vhost_common


    @endif

</VirtualHost>


@endforeach

