#!/bin/sh

cd /var/www/monitoring/nc/
/bin/nc -i 2 38.108.132.98 23 < /var/www/monitoring/nc/primaryChannel.nc > /var/www/monitoring/nc/primaryChannel.log
/usr/bin/php /var/www/monitoring/nc/parse.php
