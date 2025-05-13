<?php

namespace Modules\ProvBase\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\ProvBase\Entities\Modem;

class ModemDhcpLog extends Component
{
    public $modemId = null;

    /** @var Modules\ProvBase\Entities\Modem */
    private $modem;

    public function mount()
    {
        // \Log::debug(__CLASS__.'::'.__FUNCTION__);
    }

    #[Computed()]
    protected function dhcplog()
    {
        $this->modem = Modem::find($this->modemId);

        // if (config('app.env') == 'local') {
        //     return $this->getExampleLog();
        // }

        $onlineStatus = $this->modem->onlineStatus();
        $ip = $onlineStatus['ip'];

        $logs = $this->modem->getDhcpLogEntries($ip);

        return $logs ?: trans('messages.noData');
    }

    /**
     * Return logs for local development or test environment
     */
    private function getExampleLog(): array
    {
        sleep(4);

        $mac = strtolower($this->modem->mac);

        return [
            0 => "Apr 30 01:43:55 rocky dhcpd[1689530]: Added reverse map from 226.119.0.10.in-addr.arpa. to cm-{$this->modem->id}.nmsprime.test.",
            1 => "Apr 30 01:43:55 rocky dhcpd[1689530]: Added new forward map from cm-{$this->modem->id}.nmsprime.test. to 10.0.119.226",
            2 => "Apr 30 01:43:55 rocky dhcpd[1689530]: DHCPACK on 10.0.119.226 to $mac via eth0",
            3 => "Apr 30 01:43:55 rocky dhcpd[1689530]: DHCPREQUEST for 10.0.119.226 from $mac via eth0",
        ];
    }

    public function placeholder()
    {
        return '<div></div>';
    }

    public function render()
    {
        return view('provbase::livewire.modem-dhcp-log');
    }
}
