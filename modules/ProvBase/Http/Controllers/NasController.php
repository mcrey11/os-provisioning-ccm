<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Modules\ProvBase\Entities\Nas;
use Modules\ProvBase\Entities\RadAcct;

class NasController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS NAS Devices';
        $headline = '';

        $nasList = Nas::orderBy('nasname')->paginate(25);

        return \View::make('provbase::Radius.Nas.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'nasList'))
        );
    }

    public function show($id)
    {
        $view_header = 'NAS Detail';
        $headline = '';

        $nas = Nas::findOrFail($id);
        $recentSessions = RadAcct::where('nasipaddress', $nas->nasname)
            ->orderByDesc('acctstarttime')
            ->limit(20)
            ->get();

        return \View::make('provbase::Radius.Nas.show',
            $this->compact_prep_view(compact('view_header', 'headline', 'nas', 'recentSessions'))
        );
    }
}
