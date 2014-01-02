#!/bin/sh

DB_Config_Encoder
killall docsis_server
/usr/local/sbin/docsis_server
sleep 1
ps waux | grep docsis_server
