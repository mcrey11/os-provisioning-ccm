<?php

use Database\Migrations\BaseMigration;

return new class extends BaseMigration
{
    public $migrationScope = 'system';

    private $path = '/etc/icinga2/conf.d/nmsprime';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $commands = [
            '/usr/bin/mkdir -p '.$this->path,
            '/usr/bin/chmod 2750 '.$this->path,
            '/usr/bin/chown root:icinga '.$this->path,
            '/usr/bin/mkdir -p '.$this->path.'/netelements',
            '/usr/bin/chmod o-rwx '.$this->path.'/netelements',
        ];
        foreach ($commands as $command) {
            exec($command);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        exec('/usr/bin/rm -rf '.$this->path.'/netelements');
    }
};
