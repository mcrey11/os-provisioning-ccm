<p align="center">
<a target="_blank" href="https://nmsprime.com"><img src="https://github.com/cablelabs/os-provisioning/raw/dev/public/images/nmsprime-logo.png" alt="NMS Prime Logo" title="NMS Prime — CRM, BSS & OSS Platform for Telcos and ISPs" width="250"/></a> <b>hosted</b> by
<a target="_blank" href="https://cablelabs.com"><img src="http://www.displaysummit.com/wp-content/uploads/2019/07/Cable-Labs-Logo-Red.png" alt="CableLabs Logo" width="250"/></a>
</p>
<br>

[![Open in Visual Studio Code](https://img.shields.io/static/v1?logo=visualstudiocode&label=&message=Open%20in%20Visual%20Studio%20Code&labelColor=2c2c32&color=007acc&logoColor=007acc)](https://github.dev/cablelabs/os-provisioning)
[![Crowdin](https://d322cqt584bo4o.cloudfront.net/nmsprime/localized.svg)](https://crowdin.com/project/nmsprime)
[![StyleCI](https://github.styleci.io/repos/109520753/shield?branch=dev)](https://github.styleci.io/repos/109520753)

# NMS Prime — Community Edition

[NMS PRIME](https://nmsprime.com) is a modular **CRM, BSS, and OSS platform for telcos and ISPs** — built from ISPs, for ISPs. Run your core stack yourself, on **premises or in the cloud**, and keep full control over provisioning, billing, customer care, and operations in one extensible system.

The **Community Edition** in this repository delivers the complete **OSS Provisioning layer**: technology- and vendor-agnostic service activation and CPE management for **DOCSIS**, **FTTH**, **FTTx**, **DSL**, **WiFi**, and other access technologies. Extend the platform with enterprise modules for **CRM**, **billing**, **ticketing**, **monitoring**, and more — or build your own apps on top of the open-source core.

<div align="center"><a href="https://nmsprime.com"><img src="https://github.com/cablelabs/os-provisioning/raw/dev/public/images/apps_row.png" alt="NMS Prime Application Marketplace" title="NMS Prime — One Platform. Every Core Function."/></a></div><br>

## 📱 Community Edition — OSS Provisioning
📡 **Provisioning** — full OSS layer for any access technology<br>
📞 **VoIP Provisioning**<br>
🎛️ **Control & SNMP** — network element management<br>
🏢 **Enterprise modules** — CRM, BSS, billing, workforce & more → [nmsprime.com](https://www.nmsprime.com)

## ⚡ OSS Provisioning Capabilities
**Access-Agnostic Activation**<br>
📶 **DOCSIS** 1.0, 1.1, 2.0, **3.0, 3.1**<br>
🏠 **FTTH**, **DSL**, and WiFi via **TR-069** and **RADIUS**<br>
🌐 Dual-stack **IPv4 / IPv6**<br>

**Network & Service Operations**<br>
🖧 **CMTS**, Router, **OLT**, and Switch Management via SNMP or TR-069<br>
📡 **Cable ingress detection**<br>
🗺️ Real-time **topographic maps** and entity relation diagrams<br>
⚙️ Integrated **Icinga2**, **Prometheus**, **Grafana**, and **Cacti**<br>
🎫 **Ticket System**<br>
🛠️ Generic **SNMP GUI** creator<br>
📚 [Full documentation](https://nmsprime.atlassian.net/wiki/spaces/NMS/overview)

📖 Explore the complete platform — CRM, billing, provisioning, monitoring, and more — at [nmsprime.com](https://nmsprime.com) and in the [Official Documentation](https://nmsprime.atlassian.net/wiki/spaces/NMS/overview).


## 🏗️ Architectural Concepts

NMS Prime is built on the [Laravel](https://laravel.com/) framework with [PHP 8](https://php.net) and a modern, responsive [Bootstrap](http://getbootstrap.com/) front end. The OSS layer integrates proven open-source infrastructure — not proprietary black boxes — so operators retain full control over their stack.

It is tested and developed under Rocky 9 (RHEL 9).

NMS Prime is built with standard Linux tools, like

🔌 [ISC DHCP](https://www.isc.org/downloads/dhcp/) for IPv4<br>
🌍 [Kea](https://www.isc.org/kea/) for IPv6<br>
📇 [BIND](https://linux.die.net/man/8/named)<br>
🐘 [PostgreSQL](https://www.postgresql.org/)<br>
🔔 [Icinga2](https://icinga.com/)<br>
🔍 [Prometheus](https://prometheus.io/)<br>
📊 [Grafana](https://grafana.com/)<br>
📈 [Cacti](https://www.cacti.net/)

These tools are actively developed, approved and used. See [Design Architecture](https://nmsprime.atlassian.net/wiki/x/YgWC) for more information.


## 📦 Installation

### 🐧 Community Version

**From RPM for Rocky 9 (RHEL 9)**

```bash
curl -vsL https://github.com/cablelabs/os-provisioning/raw/dev/scripts/INSTALL-REPO.sh | bash
yum install nmsprime-*
```
### 🏢 Enterprise Platform

The full NMS Prime platform adds **CRM**, **BSS**, **billing**, **dunning**, **workforce management**, and further modules on top of the OSS core. Select your applications and run them in the **Cloud** or **On-Prem**: [Enterprise Installation](https://contact.nmsprime.com)

### 👨‍💻 Developers only (source-code installation)

In order to track and install all NMS Prime dependencies, the workflow for getting a source code installation up and running starts like described above. You can use both variants (community or enterprise) to do so.

Afterwards the NMS Prime RPM packages are replaced with the GIT repository by issuing the following commands:

```bash
for module in $(rpm -qa "nmsprime-*" | grep -v '^nmsprime-repos'); do rpm -e --justdb --noscripts --nodeps "$module"; done

yum install git npm

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

cd /var/www
git clone https://github.com/cablelabs/os-provisioning nmsprimeGit
mv nmsprimeGit/.git/ nmsprime/
rm -rf nmsprimeGit/
cd nmsprime

git checkout -- .
git clean -f -x

# move enterprise apps into /root folder for reference, they are not needed for the community git version
for module in $(ls -1 modules | grep -v '^HfcReq$\|^HfcSnmp$\|^NmsMail$\|^ProvBase$\|^ProvVoip$'); do mv "$module" /root/; done

composer update
php artisan module:v6:migrate
find public/{css,js} -iname "*.br" -o -iname "*.gz"
rm -f public/mix-manifest.json
npm i && npm run dev

yum install $(for file in $(find /var/www/nmsprime -name config.cfg); do grep '^depends[[:space:]]*=' "$file" | cut -d'=' -f2- | cut -d'"' -f2; done | tr ';' '\n' | sed -e '/^$/d' -e '/^nmsprime-/d' | sort -u)

php artisan migrate
php artisan module:migrate --all
php artisan module:publish --all
php artisan bouncer:clean
php artisan nms:auth
php artisan optimize
systemctl restart supervisord
```

---
## 🤝 Contributors

📝 **How to contribute**

Please read [CONTRIBUTING](https://github.com/cablelabs/os-provisioning/blob/dev/CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

🧩 **Write your own Application**

If you want to develop your own open-source or proprietary application(s), please refer to [Write your own Application](https://nmsprime.atlassian.net/wiki/x/fQKC)

📖 **History & Motivation**

NMS Prime started as a German ISP initiative with a clear goal: a technology- and vendor-agnostic **OSS/BSS reference platform** that puts operators back in control of their core systems — from provisioning across DOCSIS, FTTH, and WiFi to the broader CRM and billing stack telcos need to grow profitably. Join the community and help shape the future of ISP software.

🗺️ **Roadmap**

See [Upcoming Developments](https://nmsprime.atlassian.net/wiki/spaces/NMS/pages/8522020/Roadmap)

⚖️ **License**

This project is licensed under the [Apache-2.0](https://github.com/cablelabs/os-provisioning/blob/dev/LICENSE) file for details. For more information: [License Article](https://nmsprime.atlassian.net/wiki/spaces/NMS/pages/8533119/EULA)
