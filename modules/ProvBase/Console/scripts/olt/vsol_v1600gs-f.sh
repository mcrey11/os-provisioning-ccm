#!/bin/bash
# VSOL GPON OLT (V1600GS-F) auto-provisioning script
# Provisions newly discovered ONUs with IPoE VLAN configuration
#
# Usage: $0 hostname username password ssh_port vlan
# Called by NetGw::runSshAutoProv() with: ip username password port vlan

if [ "$#" -ne 5 ]; then
    echo "Usage: $0 hostname username password ssh_port vlan" >&2
    exit 1
fi

hostname="$1"
username="$2"
password="$3"
port="$4"
vlan="$5"

# Step 1: Discover unconfigured ONUs via auto-find
onu_list=$(expect <<- EOF |
set timeout 15

spawn ssh -o StrictHostKeyChecking=no -p "$port" "$username@$hostname"
expect {
    "*assword:" { send "$password\r" }
    timeout { exit 1 }
}
expect "*#"
send "show onu auto-find\r"
expect "*#"
send "exit\r"
expect eof
EOF
grep -E '^\s+[0-9]+/' | awk '{print $1}')

if [ -z "$onu_list" ]; then
    echo "No unconfigured ONUs found on $hostname"
    exit 0
fi

echo "Found unconfigured ONUs on $hostname:"
echo "$onu_list"

# Step 2: For each ONU, register and configure IPoE VLAN
cmd=''
while read onu_entry; do
    # Parse PON port and ONU ID from entry like "0/1:1" (pon_port:onu_id)
    pon_port=$(echo "$onu_entry" | cut -d':' -f1)
    onu_id=$(echo "$onu_entry" | cut -d':' -f2)

    if [ -z "$pon_port" ] || [ -z "$onu_id" ]; then
        echo "Skipping invalid entry: $onu_entry"
        continue
    fi

    echo "Provisioning ONU $onu_id on PON $pon_port..."

    cmd+="configure terminal\r"
    # Register ONU with auto-learn profiles
    cmd+="interface gpon $pon_port\r"
    cmd+="onu $onu_id activate\r"
    cmd+="exit\r"
    # Configure TCONT and GEM port directly
    cmd+="interface gpon $pon_port\r"
    cmd+="onu $onu_id tcont 1 dba vsol-dba\r"
    cmd+="onu $onu_id gemport 1 tcont 1\r"
    cmd+="onu $onu_id service 1 gemport 1 vlan $vlan\r"
    cmd+="onu $onu_id service-port 1 gemport 1 uservlan $vlan vlan $vlan\r"
    cmd+="onu $onu_id portvlan eth 1 mode tag vlan $vlan\r"
    cmd+="exit\r"
done <<< "$onu_list"

# Step 3: Execute all commands
expect <<- EOF
set timeout 15

spawn ssh -o StrictHostKeyChecking=no -p "$port" "$username@$hostname"
expect {
    "*assword:" { send "$password\r" }
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
