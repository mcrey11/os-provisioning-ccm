env='/etc/nmsprime/env'

cd '/var/www/nmsprime'
rm -rf /var/www/nmsprime/bootstrap/cache/*
php artisan config:cache
php artisan module:publish --all
php artisan module:migrate --all
php artisan bouncer:clean
php artisan nms:auth
php artisan route:cache
php artisan view:clear

# on HA machines: clean up
[ -e /var/www/nmsprime/modules/ProvHA/Console/CleanUpSlaveCommand.php ] &&
	php artisan module:list | grep -i provha | grep -i enabled &&
	php artisan provha:clean_up_slave

# reread supervisor config and restart affected processes
systemctl restart supervisord

systemctl reload httpd

chown -R apache storage bootstrap/cache
systemd-tmpfiles --create

# make .env files readable for apache
chgrp -R apache "$env"
chmod 640 "$env"/*.env
# only allow root to read/write mysql root credentials
chown root:root "$env/root.env"
chmod 600 "$env/root.env"
