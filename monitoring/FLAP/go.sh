#!/bin/sh

/bin/nc 38.108.136.1 23 < /var/www/monitoring/FLAP/get.nc > /var/www/monitoring/FLAP/LIST
/usr/bin/php /var/www/monitoring/FLAP/parse.php  >/tmp/flapchecker 2>/tmp/flapchecker
