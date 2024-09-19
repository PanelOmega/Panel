#=========================================================================#
# OMEGA PANEL - Default Web Domain Template                               #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://panelomega.com/docs/server-administration/web-templates.html    #
# OS: AlmaLinux                                                            #
#=========================================================================#

ServerRoot "/etc/my-apache"

Listen 80

Include conf.modules.d/*.conf

User nobody
Group nobody

ServerAdmin root@localhost


<Directory "/">
AllowOverride All
Options ExecCGI FollowSymLinks IncludesNOEXEC Indexes
</Directory>

<IfModule dir_module>
    DirectoryIndex index.php index.php8 index.php7 index.php5 index.perl index.pl index.plx index.ppl index.cgi index.jsp index.jp index.phtml index.shtml index.xhtml index.html index.htm index.js
</IfModule>

<Files ".ht*">
Require all denied
</Files>


ErrorLog "logs/error_log"
LogLevel debug

<IfModule log_config_module>

    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
    LogFormat "%h %l %u %t \"%r\" %>s %b" common

    <IfModule logio_module>
        # You need to enable mod_logio.c to use %I and %O
        LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %I %O" combinedio
    </IfModule>

    CustomLog "logs/access_log" combined
</IfModule>


<IfModule mime_module>

    TypesConfig /etc/mime.types

    AddType application/x-compress .Z
    AddType application/x-gzip .gz .tgz

    AddType text/html .shtml
    AddOutputFilter INCLUDES .shtml
</IfModule>


AddDefaultCharset UTF-8

<IfModule mime_magic_module>
    MIMEMagicFile conf/magic
</IfModule>


EnableSendfile on
IndexOptions FancyIndexing HTMLTable VersionSort

<Directory "/home/*/public_html">
AllowOverride FileInfo AuthConfig Limit Indexes
Options MultiViews Indexes SymLinksIfOwnerMatch IncludesNoExec
Require method GET POST OPTIONS
</Directory>

