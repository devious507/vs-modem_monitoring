#!/bin/sh

/bin/nc 38.108.136.1 23 < /var/www/monitoring/flapcheck/getflaps.nc > /var/www/monitoring/flapcheck/FLAP
/usr/bin/php /var/www/monitoring/flapcheck/parse.php
