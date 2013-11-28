#/bin/bash -l
cd /home/web/evebloggers/utils/
/usr/local/bin/php /home/web/evebloggers/utils/loadblogentries.php >/dev/null
#/usr/local/bin/php /home/web/evebloggers/utils/loadblogentries.php
touch /home/web/evebloggers/smarty/templates/nav.tpl
touch /home/web/evebloggers/smarty/templates/feed.tpl
