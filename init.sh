#!/bin/bash

set -e


wp config create --dbuser="root" --dbhost="$DB_HOST" --dbname="$MYSQL_DATABASE" --dbpass="$MYSQL_ROOT_PASSWORD" && chmod 777 -R  /var/www/html/wp-config.php

mysql -u root --password="$MYSQL_ROOT_PASSWORD" -h db -e "create database IF NOT EXISTS $MYSQL_DATABASE"
echo "$URL"
wp core install --url="$URL" --title='Test' --admin_user=admin --admin_password=admin --admin_email=admin@admin.com

wp plugin install ari-adminer --activate

wp rewrite structure '%postname%'
wp rewrite flush
wp plugin activate --all

/etc/init.d/cron start

#Run base image entrypoint.
docker-entrypoint.sh