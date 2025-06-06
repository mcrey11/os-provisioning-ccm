#!/bin/bash
# This script makes sure that Apache has configured the appropriate intermediate certificate since
# Acme-tiny can switch intermediate certs on cert renewal
# Thx to https://www.krausmueller.de/en/2016/06/14/intermediate-certificate-for-acme-tiny/

# Check if correct intermediate cert is configured in Apache
cert=$(grep SSLCertificateFile /etc/httpd/conf.d/nmsprime-admin.conf | sed 's/ //g' | grep -v "^#" | cut -d 'e' -f4-100)
intermediate=$(openssl x509 -in $cert -text -noout | grep "Issuer: " | cut -d '=' -f4 | sed 's/ //g' | tr '[:upper:]' '[:lower:]')
grep SSLCertificateChainFile /etc/httpd/conf.d/nmsprime-admin.conf | grep -q $intermediate && exit

# Load intermediate if not already done or if outdated
intermediateFpath=/var/lib/acme/lets-encrypt-$intermediate.pem
enddate=$(openssl x509 -in $intermediateFpath -noout -enddate | cut -d '=' -f2)
enddate=$(date -d "$enddate" +%s)
date=$(date +%s)

if [ ! -f $intermediateFpath ] || [ $enddate -le $date ]; then
    url=`openssl x509 -in $cert -text -noout | grep "CA Issuers - URI:" | cut -d":" -f2,3`
    wget -O - $url > /tmp/intermediate.der
    openssl x509 -in /tmp/intermediate.der -inform der -outform pem -out $intermediateFpath
fi

# Replace intermediate and restart Apache
escapedIntermediateFpath=$(sed 's/[\*\.\/&]/\\&/g' <<<"$intermediateFpath")
sed -i "s/SSLCertificateChainFile .*/SSLCertificateChainFile $escapedIntermediateFpath/" /etc/httpd/conf.d/nmsprime-*.conf
systemctl restart httpd
