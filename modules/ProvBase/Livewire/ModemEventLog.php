<?php

namespace Modules\ProvBase\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\ProvBase\Entities\Modem;

class ModemEventLog extends Component
{
    // public $eventlog = [];
    public $modemId = null;

    /** @var Modules\ProvBase\Entities\Modem */
    private $modem;

    public function boot()
    {
        $this->modem = Modem::find($this->modemId);
    }

    #[Computed()]
    protected function eventlog()
    {
        if (config('app.env') == 'local') {
            return $this->getExampleLog();
        }

        return $this->modem->eventlog();
    }

    /**
     * Return logs for local development or test environment
     */
    private function getExampleLog(): array
    {
        sleep(4);

        return [
            0 => [
                'Time',
                '#',
                'Text',
            ],
            2613 => [
                '2025-04-23 00:08:27',
                '1',
                'success',
                'WiFi Interface [wl0] set to Channel 1 (Side-Band Channel:N/A) - Reason:INTERFERENCE',
            ],
            2612 => [
                '2025-04-22 23:53:21',
                '1',
                'success',
                'WiFi Interface [wl0] set to Channel 11 (Side-Band Channel:N/A) - Reason:INTERFERENCE',
            ],
            2611 => [
                '2025-04-22 23:12:48',
                '1',
                'danger',
                'DHCP RENEW WARNING - Field invalid in response v4 option;CM-MAC='.$this->modem->mac.';CMTS-MAC=88:43:e1:47:41:38;CM-QOS=1.1;CM-VER=3.0;',
            ],
            2610 => [
                '2025-04-22 21:37:35',
                '1',
                'success',
                'WiFi Interface [wl0] set to Channel 1 (Side-Band Channel:N/A) - Reason:INTERFERENCE',
            ],
        ];
    }

    public function placeholder()
    {
        return view('Components.spinner');
    }

    public function render()
    {
        return view('provbase::livewire.modem-event-log');
    }
}
