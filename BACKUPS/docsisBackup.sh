#!/bin/sh

mysqldump dhcp_server --lock-all-tables > dbbackup.db
gzip --best dbbackup.db
scp dbbackup.db.gz root@38.108.136.8:/var/www/BACKUPS

mkdir etc
mkdir root
mkdir root/bin
mkdir usr
mkdir usr/local
mkdir usr/local/etc

cp /etc/docsis-server.conf etc/
cp /root/bin/docsis_daemon.Watchdog root/bin
cp /root/bin/resetServer.sh root/bin
cp /usr/local/etc/docsis-server.conf usr/local/etc/

#tar -zcvf /rsync/docsisBackup.tar.gz /root/dbbackup.db /etc/docsis-server.conf /root/bin/docsisBackup.sh /root/bin/docsis_daemon.Watchdog /root/bin/resetServer.sh /root/bin/docsisRestore.sh /usr/local/etc/docsis-server.conf /cm_boot /etc/rsyncd.conf /var/www
