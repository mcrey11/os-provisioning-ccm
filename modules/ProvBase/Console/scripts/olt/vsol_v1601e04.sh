#!/bin/bash
# VSOL EPON OLT (V1601E04) auto-provisioning script
# Provisions newly discovered ONUs with IPoE VLAN configuration
#
# Usage: $0 hostname username password telnet_port vlan
# Called by NetGw::runSshAutoProv() with: ip username password port vlan
# Note: port=23 for telnet (SSH is filtered on this OLT)

if [ "$#" -ne 5 ]; then
    echo "Usage: $0 hostname username password telnet_port vlan" >&2
    exit 1
fi

hostname="$1"
username="$2"
password="$3"
port="$4"
vlan="$5"

# Step 1: Discover unconfigured ONUs via telnet
onu_list=$(expect <<- EOF |
set timeout 15

spawn telnet "$hostname"
expect {
    "login:" { send "$username\r" }
    "Username:" { send "$username\r" }
    timeout { exit 1 }
}
expect {
    "password:" { send "$password\r" }
    "Password:" { send "$password\r" }
    timeout { exit 1 }
}
expect "*#"
send "show onu discover all\r"
expect "*#"
send "exit\r"
expect eof
EOF
grep -E '^\s+[0-9]+' | awk '{print $1}' | head -20)

if [ -z "$onu_list" ]; then
    echo "No unconfigured ONUs found on $hostname"
    exit 0
fi

echo "Found ONUs on $hostname:"
echo "$onu_list"

# Step 2: For each ONU, register and configure IPoE VLAN
cmd=''
while read onu_entry; do
    # Parse PON port and ONU ID
    pon_port=$(echo "$onu_entry" | cut -d':' -f1)
    onu_id=$(echo "$onu_entry" | cut -d':' -f2)

    if [ -z "$pon_port" ] || [ -z "$onu_id" ]; then
        # Try alternate format: just ONU ID with PON from context
        onu_id="$onu_entry"
        pon_port="0/1"
    fi

    echo "Provisioning ONU $onu_id on EPON PON $pon_port..."

    cmd+="configure terminal\r"
    cmd+="interface epon $pon_port\r"
    cmd+="onu $onu_id tcont 1 dba vsol-dba\r"
    cmd+="onu $onu_id gemport 1 tcont 1\r"
    cmd+="onu $onu_id service 1 gemport 1 vlan $vlan\r"
    cmd+="onu $onu_id service-port 1 gemport 1 uservlan $vlan vlan $vlan\r"
    cmd+="onu $onu_id portvlan eth 1 mode tag vlan $vlan\r"
    cmd+="exit\r"
done <<< "$onu_list"

# Step 3: Execute all commands via telnet
expect <<- EOF
set timeout 15

spawn telnet "$hostname"
expect {
    "login:" { send "$username\r" }
    "Username:" { send "$username\r" }
    timeout { exit 1 }
}
expect {
    "password:" { send "$password\r" }
    "Password:" { send "$password\r" }
    timeout { exit 1 }
}
expect "*#"
send "$cmd"
send "write\r"
expect "*#"
send "exit\r"
expect eof
EOF

echo "Auto-provisioning complete on $hostname"
