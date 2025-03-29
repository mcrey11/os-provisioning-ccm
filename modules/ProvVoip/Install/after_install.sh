# change owner
chown -R apache:dhcpd /etc/dhcp-nmsprime
sed -i '/^#.*mta.conf";/s/^#//' /etc/dhcp-nmsprime/dhcpd.conf

firewall-cmd --reload

# Note: this script runs before module_after_install.sh - active state must be set before artisan cmds shall run
php /var/www/nmsprime/artisan module:migrate --all
php /var/www/nmsprime/artisan provvoip:update_carrier_code_database
php /var/www/nmsprime/artisan provvoip:update_ekp_code_database
# Currently only implemented for use with ProvVoipEnvia module - this would fail as it would run before envia module is installed
# php /var/www/nmsprime/artisan provvoip:update_trc_class_database

# Enable syslog reception for MTAs
sed -i 's/^#module(load="imudp")/module(load="imudp")/' /etc/rsyslog.conf
sed -i 's/^#input(type="imudp" port="514")/input(type="imudp" port="514")/' /etc/rsyslog.conf
systemctl restart rsyslog.service
