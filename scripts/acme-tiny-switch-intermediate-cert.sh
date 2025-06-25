#!/bin/bash
# This script makes sure that Apache has configured the appropriate intermediate certificate since
# Acme-tiny can switch intermediate certs on cert renewal
# Thx to https://www.krausmueller.de/en/2016/06/14/intermediate-certificate-for-acme-tiny/

function downloadIntermediateCert() {
    url=$(openssl x509 -in $cert -text -noout | grep "CA Issuers - URI:" | cut -d":" -f2-)
    wget -O /tmp/intermediate.der $url
    openssl x509 -in /tmp/intermediate.der -inform der -outform pem -out $intermediateFpath
}

# Check if correct intermediate cert is configured in Apache
cert=$(grep -v '\s*#' /etc/httpd/conf.d/nmsprime-admin.conf | grep SSLCertificateFile | awk '{print $2}')
intermediate=$(openssl x509 -in $cert -issuer -noout | cut -d '=' -f5 | sed 's/\s//g' | tr '[:upper:]' '[:lower:]')
grep SSLCertificateChainFile /etc/httpd/conf.d/nmsprime-admin.conf | grep -q $intermediate && exit

# Load intermediate if not already done or if outdated
intermediateFpath=/var/lib/acme/lets-encrypt-$intermediate.pem

if [ ! -f $intermediateFpath ]; then
    downloadIntermediateCert
else
    enddate=$(openssl x509 -in $intermediateFpath -noout -enddate | cut -d '=' -f2)
    enddate=$(date -d "$enddate" +%s)
    date=$(date +%s)

    if [ $enddate -le $date ]; then
        downloadIntermediateCert
    fi
fi

# Replace intermediate and restart Apache
escapedIntermediateFpath=$(sed 's/[\*\.\/&]/\\&/g' <<<"$intermediateFpath")
sed -i "s/SSLCertificateChainFile .*/SSLCertificateChainFile $escapedIntermediateFpath/" /etc/httpd/conf.d/nmsprime-*.conf
systemctl restart httpd
