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

use Database\Migrations\BaseMigration;

/**
 * Set config to rotate /var/log/messages daily now and log remote syslog messages to /var/log/remote
 */
return new class extends BaseMigration
{
    public $migrationScope = 'system';

    protected $tableName = 'provmon';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! is_file('/etc/rsyslog.d/remote.conf')) {
            copy(base_path('Install/files/remote.conf'), '/etc/rsyslog.d/remote.conf');

            system('systemctl restart rsyslogd');
        }

        $conf = file_get_contents('/etc/logrotate.d/nmsprime');
        if (! str_contains($conf, '/var/log/remote')) {
            $remoteConf = "\n/var/log/remote {
    daily
    missingok
    rotate 7
    create 644 apache apache
}";
            file_put_contents('/etc/logrotate.d/nmsprime', $remoteConf, FILE_APPEND);
        }

        system("sed -i '/\/log\/messages/d' /etc/logrotate.d/rsyslog");

        if (! is_file('/etc/logrotate.d/messages')) {
            copy(base_path('Install/files/messages.log'), '/etc/logrotate.d/messages');
        }

        // Logrotate configuration changes are automatically picked up on next run
        // No need to restart logrotate.service as it's not a restartable service
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        system("sed -i '/\log\/maillog/ a\/var\/log\/messages' /etc/logrotate.d/rsyslog");
        system("sed -i '/\/var\/log\/remote/,+6d' /etc/logrotate.d/nmsprime");

        if (is_file('/etc/rsyslog.d/remote.conf')) {
            unlink('/etc/rsyslog.d/remote.conf');
        }

        if (is_file('/etc/logrotate.d/messages')) {
            unlink('/etc/logrotate.d/messages');
        }

        // Logrotate configuration changes are automatically picked up on next run
        // No need to restart logrotate.service as it's not a restartable service
        system('systemctl restart rsyslogd');
    }
};
