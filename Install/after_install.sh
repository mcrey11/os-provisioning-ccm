#
# variables
#
dir='/var/www/nmsprime'
env='/etc/nmsprime/env'
pw=$(pwgen 12 1) # SQL password for user nmsprime
root_pw=$(pwgen 12 1) # SQL password for root


#
# disable SE linux
#
sed -i "s/^SELINUX=enforcing$/SELINUX=disabled/" /etc/sysconfig/selinux
sed -i "s/^SELINUX=enforcing$/SELINUX=disabled/" /etc/selinux/config
setenforce  0

# set default hostname, if none was explicitly set
if [[ "$(hostname)" == 'localhost.localdomain' ]]; then
	hostnamectl set-hostname nmslx01.nmsprime.test
fi

#
# HTTP
#
# SSL demo certificate
mkdir /etc/httpd/ssl
openssl req -new -x509 -days 3650 -nodes -batch -out /etc/httpd/ssl/httpd.pem -keyout /etc/httpd/ssl/httpd.key
chmod 440 /etc/httpd/ssl/httpd.key
chown root:apache /etc/httpd/ssl/httpd.key

# reload apache config
systemctl start httpd
systemctl enable httpd

#
# firewalld
#
# enable admin interface
firewall-cmd --add-port=8080/tcp --zone=public --permanent
firewall-cmd --reload


#
# Postgresql
#
/usr/pgsql-16/bin/postgresql-16-setup initdb
systemctl enable postgresql-16.service
systemctl start postgresql-16.service

sudo -u postgres /usr/pgsql-16/bin/psql -c "CREATE DATABASE nmsprime;"
sudo -u postgres /usr/pgsql-16/bin/psql -d nmsprime -c "
    CREATE USER nmsprime PASSWORD '$pw';
    ALTER DATABASE nmsprime OWNER TO nmsprime;
    ALTER ROLE postgres set search_path to 'nmsprime';
"
sudo -u postgres /usr/pgsql-16/bin/psql -d nmsprime < /etc/nmsprime/sql-schemas/nmsprime.pgsql

#
# mariadb
#
systemctl start mariadb
systemctl enable mariadb

# populate timezone info and set php timezone based on the local one
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root mysql
zone=$(timedatectl | grep 'Time zone' | cut -d':' -f2 | cut -d' ' -f2)
sed -e "s|^;date.timezone =.*|date.timezone = $zone|" \
    -e 's/^memory_limit =.*/memory_limit = 1024M/' \
    -e 's/^upload_max_filesize =.*/upload_max_filesize = 250M/' \
    -e 's/^post_max_size =.*/post_max_size = 250M/' \
    -e 's/^;upload_tmp_dir =.*/upload_tmp_dir = \/tmp/' \
    -i /etc/php.ini

sed -e "s|^#APP_TIMEZONE=|APP_TIMEZONE=$zone|" \
    -e "s/^DB_PASSWORD=$/DB_PASSWORD=$pw/" \
    -i "$env/global.env"

echo $'\n;JIT Compiler\nopcache.jit_buffer_size=100M\nopcache.jit=tracing' >> /etc/php.d/10-opcache.ini

# mysql_secure_installation - necessary for cacti
mysql -u root << EOF
UPDATE mysql.global_priv SET priv=json_set(priv, '$.authentication_string', PASSWORD('$root_pw')) WHERE User='root';
DELETE FROM mysql.global_priv WHERE User='';
DELETE FROM mysql.global_priv WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test_%';
FLUSH PRIVILEGES;
EOF

sed -i "s/^ROOT_DB_PASSWORD=$/ROOT_DB_PASSWORD=$root_pw/" "$env/root.env"

#
# Laravel
#
cd "$dir"

install -Dm640 -o apache -g root /dev/null /var/www/nmsprime/storage/logs/laravel.log
mkdir -p -m755 "$dir/storage/app/tmp/"
mkdir -p -m755 "$dir/storage/app/public/base/bg-images/"
chown -R apache "$dir/storage/"
rm -rf /var/www/nmsprime/bootstrap/cache/*
php artisan clear-compiled
php artisan optimize
php artisan storage:link

# key:generate needs .env in root dir – create symlink to our env file
ln -srf "$env/global.env" "$dir/.env"
php artisan key:generate
# remove the symlink and create empty .env with comment
rm -f "$dir/.env"
echo "# Use $env/*.env files for configuration" > "$dir/.env"

php artisan migrate
# create default user roles to be later assigned to users
php artisan auth:roles

php artisan config:cache

# user creation / group
useradd nmsprime
chgrp nmsprime /etc/nmsprime/env/ /etc/nmsprime/env/global.env
usermod -a -G nmsprime apache

# Note: needs to run last. storage/logs is only available after artisan optimize
chown -R apache storage bootstrap/cache

# make .env files readable for apache
chgrp -R apache "$env"
chmod 640 "$env"/*.env
# only allow root to read/write mysql root credentials
chown root:root "$env/root.env"
chmod 600 "$env/root.env"

# log
chmod 644 /var/log/messages
systemctl restart rsyslog
systemd-tmpfiles --create

# Supervisord
systemctl enable supervisord
systemctl start supervisord
