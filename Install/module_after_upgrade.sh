cd '/var/www/nmsprime'

# Run artisan commands only after all installed NMSPrime modules have been upgraded
tmpFile="$(mktemp)"
lastModule=1
rpm -qa nmsprime-* --queryformat '%{NAME}-%{VERSION}-%{RELEASE}\n' | sort > $tmpFile

# Get packages that not have been updated yet
read -r -a packages <<< $(cut -d '-' -f1,2 $tmpFile | uniq -c | grep 1 | grep -v repos | cut -d '1' -f2 | sed 's/^ *//')

# Check if all packages have the newest version (also new manually installed packages)
if [ ${#packages[@]} -ne 0 ]; then
    newVersion=$(grep "nmsprime-base" $tmpFile | cut -d'-' -f3 | tail -1)

    for package in ${packages[@]}; do
        packageVersion=$(grep $package $tmpFile | cut -d'-' -f3 | tail -1)
        # echo "$package - new Version: $newVersion - package Version: $packageVersion"

        if [ "$packageVersion" != "$newVersion" ]; then
            lastModule=0
        fi
    done
fi

# Migrate when all modules are upgraded
if [ $lastModule -eq 1 ]; then
    php artisan optimize:clear
    php artisan module:publish --all
    php artisan migrate
    php artisan module:migrate --all
    php artisan bouncer:clean
    php artisan auth:nms
    php artisan optimize

    # on HA machines: clean up
    [ -e /var/www/nmsprime/modules/ProvHA/Console/CleanUpSlaveCommand.php ] &&
        php artisan module:list | grep -i provha | grep -i enabled &&
        php artisan provha:clean_up_slave

    # on HA machines: process migrations
    [ -e /var/www/nmsprime/modules/ProvHA/Console/MigrateSlaveCommand.php ] &&
    php artisan module:list | grep -i provha | grep -i enabled &&
    php artisan provha:migrate_slave

    # reread supervisor config and restart affected processes
    supervisorctl update

    # finally: rebuild dhcpd/named config
    php artisan nms:dhcp

    laravelModules=$(php /var/www/nmsprime/artisan module:list | cut -d'|' -f2)
    if echo "$laravelModules" | grep -q "ProvMon"; then
        sudo -u postgres /usr/pgsql-16/bin/psql -d nmsprime -c "
            GRANT SELECT ON ALL TABLES IN SCHEMA nmsprime TO grafana;
            GRANT USAGE ON SCHEMA nmsprime TO grafana;
        "
    fi
fi

systemctl reload httpd

rm -f $tmpFile
rm -f storage/framework/sessions/*
chown -R apache storage bootstrap/cache /var/log/nmsprime
chown -R apache:dhcpd /etc/dhcp-nmsprime
systemd-tmpfiles --create
