cd '/var/www/nmsprime'

systemctl restart httpd php-fpm nmsprimed

rm -f storage/framework/sessions/*
chown -R apache storage bootstrap/cache

systemd-tmpfiles --create

php artisan clear-compiled
php artisan optimize:clear
php artisan optimize
php artisan migrate
