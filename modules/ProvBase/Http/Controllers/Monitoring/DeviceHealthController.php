<?php

namespace Modules\ProvBase\Http\Controllers\Monitoring;

use Illuminate\Support\Facades\DB;
use Modules\HfcReq\Entities\NetElement;

class DeviceHealthController extends \BaseController
{
    public function index()
    {
        $devices = NetElement::with('netelementtype')
            ->orderBy('name')
            ->get();
        
        $data = compact('devices');
        
        return view('provbase::Monitoring.DeviceHealth.index', $this->compact_prep_view($data));
    }
    
    public function show($id)
    {
        $device = NetElement::with('netelementtype')->findOrFail($id);
        
        $status = 'unknown';
        if ($device->ip) {
            $status = $this->checkDeviceStatus($device->ip);
        }
        
        $data = compact('device', 'status');
        
        return view('provbase::Monitoring.DeviceHealth.show', $this->compact_prep_view($data));
    }
    
    protected function checkDeviceStatus($ip)
    {
        try {
            $community = config('snmp.community', 'public');
            $version = SNMP_VERSION_2C;
            
            $response = @snmp2_get($ip, $community, '1.3.6.1.2.1.1.1.0', 1000000);
            
            return $response ? 'reachable' : 'unreachable';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}