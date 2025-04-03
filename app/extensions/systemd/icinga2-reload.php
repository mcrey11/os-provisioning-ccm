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

/**
 * Reloads icinga2 after validating config
 * https://icinga.com/docs/icinga-2/latest/doc/11-cli-commands/#reload-on-configuration-changes
 */
require_once '/var/www/nmsprime/app/extensions/systemd/laralog.php';

laralog('Validating icinga2 config', 'DEBUG');
exec('/usr/sbin/icinga2 daemon -C', $out, $ret);

if ($ret) {
    laralog('Icinga2 config seems to be invalid – will not reload', 'ERROR');
    exit(1);
}

laralog('Reloading icinga2.service', 'INFO');
unset($out);
exec('/usr/bin/systemctl reload icinga2.service', $out, $ret);
if ($ret) {
    laralog('Error reloading icinga2: '.implode(' -- ', $out), 'ERROR');
    exit(1);
}
