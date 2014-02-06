#!/bin/sh

/usr/bin/php /var/www/BACKUPS/dns_secure/genScript.php
/bin/sh /var/www/BACKUPS/dns_secure/iptables.sh
