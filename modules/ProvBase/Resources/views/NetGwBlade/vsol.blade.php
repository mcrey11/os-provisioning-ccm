<?php
/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
?>
{{--

The generic VSOL OLT config blade (GPON V1600GS-F / EPON V1601E04)

--}}
enable
configure terminal

vlan {!!$cb->mgmt_vlan!!},{!!$cb->customer_vlan!!}

interface vlan {!!$cb->mgmt_vlan!!}
  ip address {{$cb->ip}} {!!$cb->netmask!!}
exit

ip route 0.0.0.0/0 {!!$cb->prov_ip!!}

hostname {{$cb->hostname}}

username {{$cb->username}} password 0 {{$cb->password}} privilege 15

interface gigabitethernet 0/9
  switchport mode trunk
  switchport trunk vlan {!!$cb->mgmt_vlan!!},{!!$cb->customer_vlan!!}
exit

interface gpon 0/1
  onu auto-learn
exit

profile onu id 10 name vsol-onu
  port-num eth 1
  commit
exit

profile dba id 10 name vsol-dba
  type 4 maximum 1024000
  commit
exit

profile srv id 10 name vsol-srv-ipoe
  portvlan eth 1 mode tag vlan {!!$cb->customer_vlan!!}
  commit
exit

profile line id 10 name vsol-line-ipoe
  tcont 1 name 1 dba vsol-dba
  gemport 1 tcont 1 gemport_name 1
  service internet gemport 1 vlan {!!$cb->customer_vlan!!}
  service-port 1 gemport 1 uservlan {!!$cb->customer_vlan!!} vlan {!!$cb->customer_vlan!!}
  commit
exit

interface gpon 0/1
  onu auto-learn srv-profile name vsol-srv-ipoe
  onu auto-learn line-profile name vsol-line-ipoe
exit

snmp-server community {!!$cb->snmp_rw!!} rw
snmp-server community {!!$cb->snmp_ro!!} ro

ssh server enable

write

end
