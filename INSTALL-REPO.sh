#!/bin/bash

# add epel
yum install -y epel-release

# add NMS Prime repos
yum install -y https://repo.nmsprime.com/rpm/misc/nmsprime-repos-latest.noarch.rpm

# TODO: investigate why this is not part of the default repos
yum install https://repo.almalinux.org/almalinux/9/devel/x86_64/os/Packages/freeradius-postgresql-3.0.21-26.el9.x86_64.rpm

# clean & update
yum clean all && yum update -y
