#!/bin/bash
# Clean up DB by removing outdated modems and its related MTAs, PNs and monitoring data
batchsize=100
date='2025-02-01'
#psqlcmd='/usr/pgsql-13/bin/psql'
psqlcmd='psql'
basequery="from nmsprime.modem where deleted_at is not null and deleted_at < '$date'"
cd /tmp
total=$(sudo -u postgres $psqlcmd nmsprime -c "select count(id) $basequery" | grep -v "^\-\|^(\|count\|^$" | tr -d ' ')
counter=0
#tables="_timescaledb_internal._materialized_hypertable_2 _timescaledb_internal._materialized_hypertable_3 _timescaledb_internal._materialized_hypertable_4"
tables='modems modems_summary_30minutes modems_summary_2hours modems_summary_24hours'

echo "0/$total"

while (true); do
    ids=$(sudo -u postgres $psqlcmd nmsprime -c "select id $basequery limit $batchsize;" | grep -v "^\-\|id\|^(" | tr '\n' ',' | tr -d ' ' | sed 's/,,$//')

#echo $ids
#exit

    counter=$((counter + $batchsize))
    echo -e "\e[1A\e[K$counter/$total"

    if [ $ids == ',' ]; then
        echo "No deleted modems anymore"
        break
    fi

    query=''
    for table in $(echo $tables); do
        query="$query delete from $table where device_id in ($ids);"
    done

    # Delete
    sudo -u postgres $psqlcmd nmsprime -q -c "$query"
    sudo -u postgres $psqlcmd nmsprime -q -c "delete from nmsprime.modem where id in ($ids);"
done

sudo -u postgres $psqlcmd nmsprime -c "delete from nmsprime.mta where modem_id not in (Select id from nmsprime.modem);"
sudo -u postgres $psqlcmd nmsprime -c "delete from nmsprime.phonenumber where mta_id not in (Select id from nmsprime.mta);"
