#!/bin/bash
: 'Make sure that ssh connection can be established! '
targetDb=nmsprime
test=false

test () {
    return
}

finalize () {
    php artisan optimize
    php /var/www/nmsprime/artisan nms:configfile
    php /var/www/nmsprime/artisan nms:icinga-netelement all
}

copyRootFolder () {
    scp -r $centos7Server:/root/*tinker* /root/
    scp $centos7Server:/root/*.sh /root/
    scp $centos7Server:/root/*.py /root/
}

setupBackup () {
    scp $centos7Server:/root/.aws/* /root/.aws/

    # Disable backup on old server
    ssh $centos7Server "grep -q '^#' /etc/cron.d/backup-nmsprime || sed -i 's/^/#/' /etc/cron.d/backup-nmsprime"

    # Enable backup on new server
    grep -q "^#" /etc/cron.d/backup-nmsprime && sed -i 's/^#//' /etc/cron.d/backup-nmsprime
}

migrateCacti () {
    rsync -av -e ssh $centos7Server:/var/lib/cacti/rra/ /var/lib/cacti/rra/

    ssh $centos7Server '
        auth=$(php -r ' "'" 'require_once "/etc/cacti/db.php"; echo "$database_default\n$database_password\n$database_username\n"; '"'"' | xargs);
        read -r -a auths <<< $auth
        mysqldump -u "${auths[2]}" --password="${auths[1]}" "${auths[0]}" > /tmp/cacti.sql
    '

    scp $centos7Server:/tmp/cacti.sql /tmp/cacti.sql
    ssh $centos7Server 'rm -f /tmp/cacti.sql'

    auth=$(php -r 'require_once "/etc/cacti/db.php"; echo "$database_default\n$database_password\n$database_username\n";' | xargs)
    read -r -a auths <<< $auth
    mysql -u "${auths[2]}" --password="${auths[1]}" "${auths[0]}" < /tmp/cacti.sql
}

migrateDhcp () {
    #$test || ssh $centos7Server "systemctl stop dhcpd"

    systemctl stop dhcpd
    rsync -aI -e ssh --exclude='cmts_gws/' $centos7Server:/etc/dhcp-nmsprime/ /etc/dhcp-nmsprime/
    scp $centos7Server:/var/lib/dhcpd/dhcpd*.leases /var/lib/dhcpd/
    scp -q $centos7Server:/var/lib/dhcpd/data_retention.sh /var/lib/dhcpd/data_retention.sh 2> /dev/null
    php /var/www/nmsprime/artisan nms:dhcp
    systemctl start dhcpd
}

configureFirewalld () {
    # /usr/lib/firewalld/zones are our default zones
    # /etc/firewalld has overwritten zones
    rm -f /etc/firewalld/zones/*
    scp -r $centos7Server:/etc/firewalld/services/* /etc/firewalld/services/
    scp -r $centos7Server:/etc/firewalld/zones/* /etc/firewalld/zones/
    rm -f /etc/firewalld/zones/.* /etc/firewalld/zones/*.xml.* 2>/dev/null

    # Restart firewalld only manually
    # systemctl status firewalld | grep -wq "Active: active (running)" && systemctl restart firewalld
}

prepareHttpdConf () {
    # Check if SSLCertif contains 3 entries - 2 are default self signed cert
    certs=$(ssh $centos7Server 'grep "SSLCertificate" /etc/httpd/conf.d/nmsprime-admin.conf | grep -v "#.*SSLCertificate" | wc -l')

    if [ $certs != 3 ]; then
        return
    fi

    # Assume to have called this function already when this file is present
    if [ -f /var/lib/acme/private/account.key ]; then
        return
    fi

    # Check if acme-tiny is installed on centos server
    ssh $centos7Server 'yum list installed acme-tiny'

    if [ $? != 0 ]; then
        return
    fi

    yum -y install acme-tiny

    scp $centos7Server:/var/lib/acme/private/account.key /var/lib/acme/private/account.key
    scp $centos7Server:/var/lib/acme/csr/* /var/lib/acme/csr/
    chown acme:acme /var/lib/acme/private/account.key
    chmod 0400 /var/lib/acme/private/account.key

    adminFile=/etc/httpd/conf.d/nmsprime-admin.conf
    cccFile=/etc/httpd/conf.d/nmsprime-ccc.conf
    acsFile=/etc/httpd/conf.d/nmsprime-acs.conf

    sed -i 's/[^#] SSLCertificate/  # SSLCertificate/' $adminFile
    #lineAdmin=$(grep -n "SSLCertificate" $adminFile | tail -1 | cut -d ':' -f1)
    if [ -f $cccFile ]; then
        sed -i 's/[^#] SSLCertificate/  # SSLCertificate/' $cccFile
        #lineCcc=$(grep -n "SSLCertificate" $cccFile | tail -1 | cut -d ':' -f1)
    fi

    certs=$(ssh $centos7Server "grep SSLCertificate $adminFile | grep -v \"#.*SSLCertificate\" | cut -d '/' -f2-10")

    for cert in $certs; do
        fpath="/$cert"
        dir=$(dirname $fpath)
        test -d $dir && mkdir -p $dir

        test -f $fpath && scp $centos7Server:$fpath $fpath
    done

    chown acme:acme /var/lib/acme/certs/*
    chgrp apache /etc/pki/tls/private/*

    systemctl enable --now acme-tiny
}

mergeNmsEnv () {
    # global.env - copy map API keys
    for key in $(echo "GOOGLE_API_KEY HERE_GEOCODE_API_KEY HERE_JS_API_KEY OSM_NOMINATIM_EMAIL"); do
        entry=$(ssh $centos7Server "grep $key /etc/nmsprime/env/global.env");
        sed -i "s/$key=.*/$entry/" /etc/nmsprime/env/global.env
    done

    # scp ticket.env
    rsync -e ssh $centos7Server:/etc/nmsprime/env/ticket.env /etc/nmsprime/env/ticket.env
}

migrateGenieAcs () {
    # MongoDB
    ssh $centos7Server "test -e /usr/bin/mongodump" || return
    ssh $centos7Server "mongodump --db=genieacs --gzip --archive=/tmp/genieacs.gz"
    scp $centos7Server:/tmp/genieacs.gz /tmp/
    ssh $centos7Server "rm -f /tmp/genieacs.gz"
    systemctl stop genieacs-{cwmp,fs,nbi,ui}

    # TODO?: /etc/genieacs/genieacs.env

    yum list installed | grep "mongodb-mongosh" -q || yum install -y mongodb-mongosh
    # mongodump --db=genieacs --gzip --archive=/tmp/genieacs.bak.gz
    mongosh --eval "use genieacs" --eval "db.dropDatabase()" --eval "use genieacs"

    mongorestore --verbose --gzip --archive=/tmp/genieacs.gz
    systemctl start genieacs-{cwmp,fs,nbi,ui}

    # Config in /etc
    ssh $centos7Server 'test $(grep GENIEACS_CWMP_PORT /etc/genieacs/genieacs.env | cut -d '"'"'='"'"' -f2) = "7548" || echo "PLEASE merge /etc/genieacs/genieacs.env"'
}

migrateGrafana () {
    return
    # /etc/ ini ?
}

migrateIcinga () {
    ssh $centos7Server 'su - postgres -c "/usr/pgsql-13/bin/pg_dump -a icinga2" > /tmp/icinga2.psql'

    scp $centos7Server:/tmp/icinga2.psql /tmp/
    ssh $centos7Server "rm -rf /tmp/icinga2.psql"

    tables=$(sudo -u postgres psql icinga2 -c "\dt" | grep table | cut -d '|' -f2 | sed 's/ //g')

    for table in $tables; do
        if [ $table = "icinga_dbversion" ]; then
            continue
        fi

        sudo -u postgres psql icinga2 -c "truncate $table cascade"
    done

    sudo -u postgres psql icinga2 < /tmp/icinga2.psql
}

migrateLogs () {
    scp $centos7Server:/var/log/nmsprime/tftpd-cm.log /var/log/nmsprime/tftpd-cm.log
    systemctl restart rsyslog
}


migrateNamed () {
    ssh $centos7Server "rndc sync -clean"
    rsync -e ssh $centos7Server:/etc/resolv.conf /etc/resolv.conf

    systemctl stop named
    scp $centos7Server:/etc/named-nmsprime.conf /etc/named-nmsprime.conf
    scp $centos7Server:/var/named/dynamic/* /var/named/dynamic/
    rm -f /var/named/dynamic/*.jnl
    systemctl start named
}

migrateNtp () {
    scp $centos7Server:/etc/chrony.d/nmsprime.conf /etc/chrony.d/nmsprime.conf
    systemctl restart chronyd
}

prepareMonitoringSchema () {
    sudo -u postgres psql $targetDb -c "DROP EXTENSION IF EXISTS timescaledb CASCADE; DROP SCHEMA monitoring CASCADE;"
    sudo -u postgres psql $targetDb -c "
        set search_path='public';
        CREATE EXTENSION IF NOT EXISTS timescaledb;

        CREATE SCHEMA monitoring;
        CREATE TABLE monitoring.modems (time TIMESTAMPTZ NOT NULL DEFAULT NOW(), device_id INT NOT NULL, min_ds_pwr REAL NULL, avg_ds_pwr REAL NULL, max_ds_pwr REAL NULL, min_ds_snr REAL NULL, avg_ds_snr REAL NULL, max_ds_snr REAL NULL, min_mu_ref REAL NULL, avg_mu_ref REAL NULL, max_mu_ref REAL NULL, min_us_pwr REAL NULL, avg_us_pwr REAL NULL, max_us_pwr REAL NULL, min_us_snr REAL NULL, avg_us_snr REAL NULL, max_us_snr REAL NULL, t3_timeout REAL NULL, t4_timeout REAL NULL, corrected REAL NULL, uncorrectable REAL NULL, in_octets REAL NULL, out_octets REAL NULL, ds_attn REAL NULL, us_attn REAL NULL, ds_sync REAL NULL, us_sync REAL NULL, ds_attain REAL NULL, us_attain REAL NULL, link_retrain REAL NULL);

        -- chunk_time_interval => INTERVAL '1 day' or less instead of default 7 days?
        SELECT create_hypertable('monitoring.modems', 'time', partitioning_column => 'device_id', number_partitions => 1);

        CREATE MATERIALIZED VIEW monitoring.modems_summary_30minutes WITH (timescaledb.continuous) AS SELECT time_bucket(INTERVAL '30 minutes', time) AS bucket, device_id, MIN(min_ds_pwr) AS min_ds_pwr, AVG(avg_ds_pwr) AS avg_ds_pwr, MAX(max_ds_pwr) AS max_ds_pwr, MIN(min_ds_snr) AS min_ds_snr, AVG(avg_ds_snr) AS avg_ds_snr, MAX(max_ds_snr) AS max_ds_snr, MIN(min_mu_ref) AS min_mu_ref, AVG(avg_mu_ref) AS avg_mu_ref, MAX(max_mu_ref) AS max_mu_ref, MIN(min_us_pwr) AS min_us_pwr, AVG(avg_us_pwr) AS avg_us_pwr, MAX(max_us_pwr) AS max_us_pwr, MIN(min_us_snr) AS min_us_snr, AVG(avg_us_snr) AS avg_us_snr, MAX(max_us_snr) AS max_us_snr, AVG(t3_timeout) AS t3_timeout, AVG(t4_timeout) AS t4_timeout, AVG(corrected) AS corrected, AVG(uncorrectable) AS uncorrectable, AVG(in_octets) AS in_octets, AVG(out_octets) AS out_octets, AVG(ds_attn) AS ds_attn, AVG(us_attn) AS us_attn, AVG(ds_sync) AS ds_sync, AVG(us_sync) AS us_sync, AVG(ds_attain) AS ds_attain, AVG(us_attain) AS us_attain, AVG(link_retrain) AS link_retrain FROM monitoring.modems GROUP BY device_id, bucket WITH NO DATA;
        CREATE MATERIALIZED VIEW monitoring.modems_summary_2hours WITH (timescaledb.continuous) AS SELECT time_bucket(INTERVAL '2 hours', time) AS bucket, device_id, MIN(min_ds_pwr) AS min_ds_pwr, AVG(avg_ds_pwr) AS avg_ds_pwr, MAX(max_ds_pwr) AS max_ds_pwr, MIN(min_ds_snr) AS min_ds_snr, AVG(avg_ds_snr) AS avg_ds_snr, MAX(max_ds_snr) AS max_ds_snr, MIN(min_mu_ref) AS min_mu_ref, AVG(avg_mu_ref) AS avg_mu_ref, MAX(max_mu_ref) AS max_mu_ref, MIN(min_us_pwr) AS min_us_pwr, AVG(avg_us_pwr) AS avg_us_pwr, MAX(max_us_pwr) AS max_us_pwr, MIN(min_us_snr) AS min_us_snr, AVG(avg_us_snr) AS avg_us_snr, MAX(max_us_snr) AS max_us_snr, AVG(t3_timeout) AS t3_timeout, AVG(t4_timeout) AS t4_timeout, AVG(corrected) AS corrected, AVG(uncorrectable) AS uncorrectable, AVG(in_octets) AS in_octets, AVG(out_octets) AS out_octets, AVG(ds_attn) AS ds_attn, AVG(us_attn) AS us_attn, AVG(ds_sync) AS ds_sync, AVG(us_sync) AS us_sync, AVG(ds_attain) AS ds_attain, AVG(us_attain) AS us_attain, AVG(link_retrain) AS link_retrain FROM monitoring.modems GROUP BY device_id, bucket WITH NO DATA;
        CREATE MATERIALIZED VIEW monitoring.modems_summary_24hours WITH (timescaledb.continuous) AS SELECT time_bucket(INTERVAL '24 hours', time) AS bucket, device_id, MIN(min_ds_pwr) AS min_ds_pwr, AVG(avg_ds_pwr) AS avg_ds_pwr, MAX(max_ds_pwr) AS max_ds_pwr, MIN(min_ds_snr) AS min_ds_snr, AVG(avg_ds_snr) AS avg_ds_snr, MAX(max_ds_snr) AS max_ds_snr, MIN(min_mu_ref) AS min_mu_ref, AVG(avg_mu_ref) AS avg_mu_ref, MAX(max_mu_ref) AS max_mu_ref, MIN(min_us_pwr) AS min_us_pwr, AVG(avg_us_pwr) AS avg_us_pwr, MAX(max_us_pwr) AS max_us_pwr, MIN(min_us_snr) AS min_us_snr, AVG(avg_us_snr) AS avg_us_snr, MAX(max_us_snr) AS max_us_snr, AVG(t3_timeout) AS t3_timeout, AVG(t4_timeout) AS t4_timeout, AVG(corrected) AS corrected, AVG(uncorrectable) AS uncorrectable, AVG(in_octets) AS in_octets, AVG(out_octets) AS out_octets, AVG(ds_attn) AS ds_attn, AVG(us_attn) AS us_attn, AVG(ds_sync) AS ds_sync, AVG(us_sync) AS us_sync, AVG(ds_attain) AS ds_attain, AVG(us_attain) AS us_attain, AVG(link_retrain) AS link_retrain FROM monitoring.modems GROUP BY device_id, bucket WITH NO DATA;

        SELECT add_continuous_aggregate_policy('monitoring.modems_summary_30minutes', start_offset => INTERVAL '2 days' + INTERVAL '1 hour', end_offset => INTERVAL '1 hour', schedule_interval => INTERVAL '30 minutes');
        SELECT add_continuous_aggregate_policy('monitoring.modems_summary_2hours', start_offset => INTERVAL '2 days' + INTERVAL '1 hour', end_offset => INTERVAL '1 hour', schedule_interval => INTERVAL '2 hour');
        SELECT add_continuous_aggregate_policy('monitoring.modems_summary_24hours', start_offset => INTERVAL '2 days' + INTERVAL '1 hour', end_offset => INTERVAL '1 hour', schedule_interval => INTERVAL '24 hours');

        SELECT add_retention_policy('monitoring.modems', INTERVAL '2 days' + INTERVAL '1 hour');
        SELECT add_retention_policy('monitoring.modems_summary_30minutes', INTERVAL '2 weeks');
        SELECT add_retention_policy('monitoring.modems_summary_2hours', INTERVAL '2 months');
        SELECT add_retention_policy('monitoring.modems_summary_24hours', INTERVAL '2 years');

        GRANT SELECT ON monitoring.modems TO grafana;
        GRANT SELECT ON monitoring.modems_summary_30minutes TO grafana;
        GRANT SELECT ON monitoring.modems_summary_2hours TO grafana;
        GRANT SELECT ON monitoring.modems_summary_24hours TO grafana;
        GRANT USAGE ON SCHEMA public, monitoring TO grafana;

        GRANT SELECT ON ALL TABLES IN SCHEMA nmsprime TO grafana;
        GRANT USAGE ON SCHEMA nmsprime TO grafana;
    ";
}

doTimescaleInserts () {
    file=$1

    if [[ $file != /tmp/* ]]; then
        echo "Error: Please use /tmp/ directory and full path for DB files"
        return
    fi

    table=$(echo $file | sed -e 's/.pgsql//' -e 's/\/tmp\///')
    targetFile="$file.inserts"
    firstCol="bucket"
    if [ $table == 'monitoring.modems' ]; then
        firstCol="time"
    fi

    # Convert copy statement file to insert statements
    sed -e "s/^/(\'/" -e "s/\t/'\t/" -e 's/\\N/NULL/g' $file | tr '\t' ',' | sed 's/$/),/g' > $targetFile

    sudo -u postgres psql $targetDb -c "truncate $table"

    # Split file to not exceed memory limit
    rm -rf "$targetFile.d"
    mkdir -p "$targetFile.d"
    cd "$targetFile.d"
    echo "$targetFile.d"
    split -l 50000 -d $targetFile

    echo "$table < $targetFile"

    for file in $(ls); do
        # echo -ne "$file"
        truncate -s -2 $file; echo ';' >> $file
        sed -i "1 i\INSERT into $table ($firstCol,device_id,min_ds_pwr,avg_ds_pwr,max_ds_pwr,min_ds_snr,avg_ds_snr,max_ds_snr,min_mu_ref,avg_mu_ref,max_mu_ref,min_us_pwr,avg_us_pwr,max_us_pwr,min_us_snr,avg_us_snr,max_us_snr,t3_timeout,t4_timeout,corrected,uncorrectable,in_octets,out_octets,ds_attn,us_attn,ds_sync,us_sync,ds_attain,us_attain,link_retrain) VALUES" $file
        sudo -u postgres psql $targetDb < $file
    done

    cd ..
    rm -rf "$targetFile.d"
}

migrateNmsprimeDB () {
    # Dump DB on CentOS7 server
    limit=''
    if [ "$test" = true ]; then
        limit='LIMIT 1000 '
    fi

    dir=/tmp/db-dump
    remoteCmd='
        dir=/tmp/db-dump;
        mkdir -p $dir;
        chown postgres $dir;
        cd $dir;
        su - postgres -c "/usr/pgsql-13/bin/pg_dump -a nmsprime -n nmsprime -t nmsprime.roles" > $dir/roles.pgsql;
        su - postgres -c "/usr/pgsql-13/bin/pg_dump -a nmsprime -n nmsprime -T nmsprime.roles -T nmsprime.rad* -T nmsprime.nas" > $dir/nmsprime.pgsql;
        sudo -u postgres /usr/pgsql-13/bin/psql -lqt | cut -d \| -f 1 | grep -wq nmsprime_ccc && su - postgres -c "/usr/pgsql-13/bin/pg_dump -a nmsprime_ccc" > $dir/nmsprime_ccc.pgsql;
    '

    remoteCmd="$remoteCmd
        sudo -u postgres /usr/pgsql-13/bin/psql nmsprime -c \"COPY (select * from monitoring.modems $limit) TO '$dir/monitoring.modems.pgsql';\";
        sudo -u postgres /usr/pgsql-13/bin/psql nmsprime -c \"COPY (select * from monitoring.modems_summary_30minutes $limit) TO '$dir/monitoring.modems_summary_30minutes.pgsql';\";
        sudo -u postgres /usr/pgsql-13/bin/psql nmsprime -c \"COPY (select * from monitoring.modems_summary_2hours $limit) TO '$dir/monitoring.modems_summary_2hours.pgsql';\";
        sudo -u postgres /usr/pgsql-13/bin/psql nmsprime -c \"COPY (select * from monitoring.modems_summary_24hours $limit) TO '$dir/monitoring.modems_summary_24hours.pgsql';\";
    "

    ssh $centos7Server "$remoteCmd"
    scp $centos7Server:/tmp/db-dump/* /tmp/
    # ssh $centos7Server "rm -rf /tmp/db-dump/"

    echo 'Restore NMSPrime DB'
    sudo -u postgres psql nmsprime -c "DROP SCHEMA nmsprime CASCADE;"
    sudo -u postgres psql nmsprime < /etc/nmsprime/sql-schemas/nmsprime.pgsql
    tables=$(sudo -u postgres psql $targetDb -c "\dt nmsprime.*" | grep table | cut -d '|' -f2 | sed 's/ //g')
    for table in $tables; do
        sudo -u postgres psql $targetDb -c "truncate nmsprime.$table cascade"
    done

    sed -i -e 's/passwordresetinterval/password_reset_interval/' -e 's/isallnetssidebarenabled/is_all_nets_sidebar_enabled/' /tmp/nmsprime.pgsql

    # roles must be inserted first because of a foreign key constraint
    sudo -u postgres psql $targetDb < /tmp/roles.pgsql
    sudo -u postgres psql $targetDb < /tmp/nmsprime.pgsql
    if [ $(sudo -u postgres psql -lqt | cut -d \| -f 1 | grep -w nmsprime_ccc | wc -l) = 1 ]; then
        sudo -u postgres psql nmsprime_ccc -c "truncate public.cccauthuser"
        sudo -u postgres psql nmsprime_ccc < /tmp/nmsprime_ccc.pgsql
    fi

    php /var/www/nmsprime/artisan migrate
    php /var/www/nmsprime/artisan module:migrate -a

    sudo -u postgres psql $targetDb -c "
        IMPORT FOREIGN SCHEMA public limit to (radacct) from server \"nmsprime-radius\" into nmsprime;
        GRANT USAGE on FOREIGN server \"nmsprime-radius\" to nmsprime;
        ALTER foreign table nmsprime.radacct owner to nmsprime;
    "

    # Import Monitoring data - Timescale DB
    prepareMonitoringSchema

    for file in $(ls /tmp/monitoring.modems*.pgsql); do
        doTimescaleInserts $file
    done

    # rm -f /tmp/roles.pgsql /tmp/nmsprime_ccc.pgsql /tmp/nmsprime.pgsql /tmp/monitoring.modems*.pgsql
}

migrateRadius () {
    ssh $centos7Server '
        cd /tmp/
        sudo -u postgres /usr/pgsql-13/bin/psql radius -c "Alter table public.radacct drop column IF EXISTS groupname;
            Update public.radippool set pool_key=0 where pool_key is null;
            Update public.radippool set callingstationid='"''"' where callingstationid is null;
            Update public.radippool set expiry_time=NOW() where expiry_time is null";
        sudo -u postgres /usr/pgsql-13/bin/pg_dump -a radius > /tmp/radius.pgsql
    '

    scp $centos7Server:/tmp/radius.pgsql /tmp/
    ssh $centos7Server "rm -f /tmp/radius.pgsql"

    tables=$(sudo -u postgres psql radius -c "\dt" | grep table | cut -d '|' -f2 | sed 's/ //g')
    for table in $tables; do
        sudo -u postgres psql radius -c "truncate $table"
    done

    sudo -u postgres psql radius < /tmp/radius.pgsql

    #rm -f /tmp/radius.pgsql
}

migrateStorage () {
    cp -r /var/www/nmsprime/storage/ /root/

    rsync -av -e ssh --exclude='framework/' --exclude='app/tmp' $centos7Server:/var/www/nmsprime/storage/ /var/www/nmsprime/storage/
}

migrateTftp () {
    # firmware files
    rsync -a -e ssh $centos7Server:/tftpboot/fw/* /tftpboot/fw
}

prepareVm () {
    # TODO? Copy authorized keys?

    yum install -y bash-completion htop tcpdump

    grep -q "firewall" /root/.bashrc || echo 'alias firewall-list-active-zones="firewall-cmd --list-all-zones | awk '"'"'!/^[[:blank:]]/ && /active/ {p=1} !/^[[:blank:]]/ && !/active/ {p=0} p'"'"'"' >> /root/.bashrc
    grep -q "psqlcon" /root/.bashrc || echo "alias psqlcon='sudo -u postgres psql'" >> /root/.bashrc; alias psqlcon='sudo -u postgres psql'
}

syncCron () {
    rsync -a -e ssh --ignore-existing $centos7Server:/etc/cron.d/ /etc/cron.d/

    # Disable all cron jobs on old server when it's not a test run
    $test || ssh $centos7Server "sed -i -e 's/^[^#]/#/' /etc/cron.d/*"
}

# Main

for argopt in "$@"
do
	case $argopt in
		"-t")
            # Only reduces amount of timescale monitoring data to be copied to reduce time
			test=true
			;;
        *)
            centos7Server=$argopt
            ;;
		\?)
			echo "Invalid option: -$OPTARG" >&2
			exit 1
			;;
	esac
done

test
prepareVm
copyRootFolder
$test || setupBackup
configureFirewalld
mergeNmsEnv
prepareHttpdConf
migrateNtp
migrateStorage
migrateTftp
syncCron
migrateGenieAcs
migrateLogs
migrateNamed
migrateCacti
migrateIcinga
migrateRadius
migrateNmsprimeDB
migrateDhcp
finalize

echo ""
echo "ATTENTION: DON'T FORGET TO DO MANUAL TASKS!"
cd /var/www/nmsprime/
tail -18 $(realpath "$0")

# TODOs
    # Check and restart firewall manually
    # Check + Add (static) routes
    # Merge /etc/genieacs/genieacs.env
    # Merge specific entries from laravel env files
        # global.env: Geocoding
        # ticket.env: mail
    # Setup httpd ssl certificates - grep "SSLCertificate" /etc/httpd/conf.d/nmsprime-admin.conf
        # /etc/httpd/conf.d/nmsprime-acs.conf
        # /etc/httpd/conf.d/nmsprime-ccc.conf
    # Adapt voipmonitor
        # DB conf (/etc/voipmonitor.conf on Voipmon server)
        # SSH tunnel
    # systemctl start radiusd

# VERIFICATION
    # Modem analysis page
    # psqlcon radius -c "select radacctid, acctstarttime, acctstoptime, framedipaddress from radacct order by acctstarttime desc limit 10"
