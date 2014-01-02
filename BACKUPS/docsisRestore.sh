#!/bin/sh

cp etc/docsis-server.conf /etc/
cp root/bin/docsis_daemon.Watchdog /root/bin
cp root/bin/resetServer.sh /root/bin
cp usr/local/etc/docsis-server.conf /usr/local/etc


echo Unzipping DB Backup File
gunzip dbbackup.db.gz

echo Restoring to mysql
mysql < /var/www/BACKUPS/dbbackup.db

