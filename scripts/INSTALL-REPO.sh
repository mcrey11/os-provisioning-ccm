#!/bin/bash

# add epel
yum install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm
/usr/bin/crb enable

# add NMS Prime repos
yum install -y https://repo9.nmsprime.com/rpm/misc-rocky/nmsprime-repos-latest.noarch.rpm

# install PHP8.3
yum install -y yum-utils
yum module install -y php:remi-8.3

# clean & update
yum clean all && yum update -y
