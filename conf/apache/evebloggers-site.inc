# Shared by the evebloggers.com vhosts in evebloggers.conf (Included; not a site on its own).
DocumentRoot /home/web/evebloggers/htdocs
DirectoryIndex index.php

ErrorLog  "|/usr/local/sbin/cronolog /home/web/evebloggers/logs/%Y/%m/%d/error.log"
# %a = visitor IP after mod_remoteip (combined's %h would log Cloudflare's IP)
CustomLog "|/usr/local/sbin/cronolog /home/web/evebloggers/logs/%Y/%m/%d/combined.log" "%a %l %u %t \"%r\" %>s %O \"%{Referer}i\" \"%{User-Agent}i\""

<Directory /home/web/evebloggers/htdocs/>
    Require all granted
    AllowOverride None
    Options -Indexes
</Directory>

# The site sits behind Cloudflare: take the visitor IP from CF-Connecting-IP, but only when the
# request really comes from a Cloudflare range (https://www.cloudflare.com/ips/, fetched 2026-11-11).
<IfModule remoteip_module>
    RemoteIPHeader CF-Connecting-IP
    RemoteIPTrustedProxy 173.245.48.0/20
    RemoteIPTrustedProxy 103.21.244.0/22
    RemoteIPTrustedProxy 103.22.200.0/22
    RemoteIPTrustedProxy 103.31.4.0/22
    RemoteIPTrustedProxy 141.101.64.0/18
    RemoteIPTrustedProxy 108.162.192.0/18
    RemoteIPTrustedProxy 190.93.240.0/20
    RemoteIPTrustedProxy 188.114.96.0/20
    RemoteIPTrustedProxy 197.234.240.0/22
    RemoteIPTrustedProxy 198.41.128.0/17
    RemoteIPTrustedProxy 162.158.0.0/15
    RemoteIPTrustedProxy 104.16.0.0/13
    RemoteIPTrustedProxy 104.24.0.0/14
    RemoteIPTrustedProxy 172.64.0.0/13
    RemoteIPTrustedProxy 131.0.72.0/22
    RemoteIPTrustedProxy 2400:cb00::/32
    RemoteIPTrustedProxy 2606:4700::/32
    RemoteIPTrustedProxy 2803:f800::/32
    RemoteIPTrustedProxy 2405:b500::/32
    RemoteIPTrustedProxy 2405:8100::/32
    RemoteIPTrustedProxy 2a06:98c0::/29
    RemoteIPTrustedProxy 2c0f:f248::/32
</IfModule>

Header always set Strict-Transport-Security "max-age=86400"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://127.0.0.1:9000/home/web/evebloggers/htdocs/$1
