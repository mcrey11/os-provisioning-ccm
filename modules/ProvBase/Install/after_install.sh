dir='/var/www/nmsprime'
env='/etc/nmsprime/env'
source "$env/root.env"

# unfortunately dhcpd does not support hmacs other than hmac-md5
# see: https://bugs.centos.org/view.php?id=12107
dnsSecret=$(ddns-confgen -a hmac-md5 | grep secret)
radius_user='radius'
radius_psw=$(pwgen 12 1)
kea_user='kea'
kea_psw=$(pwgen 12 1)

# create folders
install -dm750 /etc/dhcp-nmsprime/cmts_gws

# change owner
chown -R apache:dhcpd /etc/dhcp-nmsprime
chown -R apache /tftpboot
chown -R named:named /var/named/dynamic
chown apache:dhcpd /etc/named-ddns.sh
chmod 750 /etc/named-ddns.sh
# set gid to let newly created files have group dhcpd - Note: uid can not be set on directory
chmod 02750 /etc/dhcp-nmsprime/

sed -i "s|^.*secret \"<DNS-PASSWORD>\";|$dnsSecret|" /etc/dhcp-nmsprime/dhcpd.conf
sed -i "s|^.*secret \"<DNS-PASSWORD>\";|$dnsSecret|" /etc/named-nmsprime.conf
dnsPw=$(echo $dnsSecret | cut -d '"' -f2)
sed -i "s|<DNS-PASSWORD>|$dnsPw|" /etc/named-ddns.sh
sudo -u postgres /usr/pgsql-16/bin/psql nmsprime -c "UPDATE nmsprime.provbase set dns_password = '$dnsPw'"

openssl rand -hex 32 > /etc/named-ddns-cpe.key
chown apache:dhcpd /etc/named-ddns-cpe.key
chmod 640 /etc/named-ddns-cpe.key

sed -i "s/<hostname>/$(hostname | cut -d '.' -f1)/" /var/named/dynamic/{nmsprime.test,in-addr.arpa}.zone

echo $'\ninclude /etc/chrony.d/*.conf' >> /etc/chrony.conf

systemctl daemon-reload

systemctl enable chronyd
systemctl enable dhcpd
systemctl enable named
systemctl enable nmsprimed
systemctl enable tftp.service # tftp.socket available as well
systemctl enable time-nmsprime

# starting dhcpd won't work now, because not all files have been populated
systemctl start chronyd
systemctl start named
systemctl start nmsprimed
systemctl start tftp.service # tftp.socket available as well
systemctl start time-nmsprime

firewall-cmd --reload

# setup FreeRADIUS
sudo -u postgres /usr/pgsql-16/bin/psql -c 'CREATE DATABASE radius;'
sudo -u postgres /usr/pgsql-16/bin/psql -d radius -c "
    CREATE USER $radius_user PASSWORD '$radius_psw';
    ALTER DATABASE radius OWNER TO $radius_user;
    ALTER ROLE postgres set search_path to 'public'";
sudo -u postgres /usr/pgsql-16/bin/psql -d radius < /etc/raddb/mods-config/sql/main/postgresql/schema.sql
sudo -u postgres /usr/pgsql-16/bin/psql -d radius < /etc/raddb/mods-config/sql/ippool/postgresql/schema.sql

for tbl in $(sudo -u postgres /usr/pgsql-16/bin/psql -qAt -c "select tablename from pg_tables where schemaname = 'public';" -d radius); do
    sudo -u postgres /usr/pgsql-16/bin/psql -d radius -c "alter table $tbl owner to $radius_user";
done

# connect nmsprime DB to foreign radius.radacct table, needed by modemparser-nmsprime
sudo -u postgres /usr/pgsql-16/bin/psql -d nmsprime -c "
    CREATE EXTENSION postgres_fdw;
    CREATE Server \"nmsprime-radius\" FOREIGN DATA WRAPPER postgres_fdw OPTIONS ( host '127.0.0.1', dbname 'radius', port '5432');
    CREATE user MAPPING FOR nmsprime server \"nmsprime-radius\" OPTIONS ( user 'radius', password '$radius_psw');
    CREATE user MAPPING FOR postgres server \"nmsprime-radius\" OPTIONS ( user 'radius', password '$radius_psw');
    IMPORT FOREIGN SCHEMA public limit to (radacct) from server \"nmsprime-radius\" into nmsprime;
    GRANT USAGE on FOREIGN server \"nmsprime-radius\" to nmsprime;
    ALTER foreign table nmsprime.radacct owner to nmsprime;
";

# adapt config files
sed -e 's/^\s*#*\s*dialect\s*=.*/\tdialect = "postgresql"/' -i /etc/raddb/mods-available/sql{,ippool}
sed -e 's/^\s*driver\s*=.*/\tdriver = "rlm_sql_${dialect}"/' \
    -e 's/^#*\s*server\s*=.*/\tserver = "localhost"/' \
    -e 's/^\s*#*\s*port\s*=.*/\tport = 5432/' \
    -e "s/^\s*#*\s*login\s*=.*/\tlogin = \"$radius_user\"/" \
    -e "s/^\s*#*\s*password\s*=.*/\tpassword = \"$radius_psw\"/" \
    -e 's/^\s*#*\s*read_clients\s*=.*/\tread_clients = yes/' \
    -i /etc/raddb/mods-available/sql

# enable FreeRADIUS modules
for module in sql sqlippool; do
    ln -srf "/etc/raddb/mods-available/$module" "/etc/raddb/mods-enabled/$module"
    chgrp -h radiusd "/etc/raddb/mods-enabled/$module"
done

# hook modules into events + disable detailed logging
sed -e '/^accounting {/a\\tsqlippool' \
    -e '/^post-auth {/a\\tsqlippool' \
    -e 's/^\s*detail/#\tdetail/' \
    -i /etc/raddb/sites-available/default

/etc/raddb/certs/bootstrap

sed -i "s/RADIUS_DB_PASSWORD=$/RADIUS_DB_PASSWORD=$radius_psw/" "$env/provbase.env"
php /var/www/nmsprime/artisan config:cache
php /var/www/nmsprime/artisan nms:raddb-repopulate

# setup Kea
sudo -u postgres /usr/pgsql-16/bin/psql -c 'CREATE DATABASE kea'
sudo -u postgres /usr/pgsql-16/bin/psql -d kea -c "
    CREATE USER $kea_user PASSWORD '$kea_psw';
    ALTER DATABASE kea OWNER TO $kea_user;
    ALTER ROLE postgres set search_path to 'public'";
/usr/sbin/kea-admin db-init pgsql -u "$kea_user" -p "$kea_psw" -n kea

install -dm750 /etc/kea/gateways6
chown -R apache /etc/kea
sed -i "s/<DB_PASSWORD>/$kea_psw/" /etc/kea/dhcp6-nmsprime.conf
sed -i "s/^KEA_DB_PASSWORD=.*$/KEA_DB_PASSWORD=$kea_psw/" /etc/nmsprime/env/provbase.env

# add 'logRotate: reopen' for /var/log/mongodb/mongod.log
sed -i '/^\s\+path:\s\+\/var\/log\/mongodb\/mongod.log$/a \ \ logRotate: reopen' /etc/mongod.conf

systemd-tmpfiles --create
systemctl enable --now genieacs-{cwmp,fs,nbi,ui} kea-dhcp6 mongod radiusd
