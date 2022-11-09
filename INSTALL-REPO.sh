#!/bin/bash

# add epel
yum install -y epel-release

# add NMS Prime repos
yum install -y https://repo.nmsprime.com/rpm/misc/nmsprime-repos-latest.noarch.rpm

# clean & update
yum clean all && yum update -y
