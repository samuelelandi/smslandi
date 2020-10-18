#!/bin/bash
bearerbox -d /etc/kannel/kannel.conf
smsbox -d /etc/kannel/kannel.conf
opensmppbox -d /etc/kannel/smpp.conf
php /usr/src/smslandi/src/smslandi.php >/tmp/smslandi.log &

