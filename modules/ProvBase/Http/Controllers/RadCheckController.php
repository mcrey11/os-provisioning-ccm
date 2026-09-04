<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class RadCheckController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS Check Attributes';
        $headline = '';

        $query = DB::connection('pgsql-radius')->table('radcheck');

        if ($search = request('search')) {
            $query->where('username', 'ilike', "%{$search}%");
        }

        $checks = $query->orderByDesc('id')->paginate(25);

        return \View::make('provbase::Radius.Check.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'checks'))
        );
    }

    public function show($id)
    {
        $view_header = 'Check Attribute Detail';
        $headline = '';

        $check = DB::connection('pgsql-radius')->table('radcheck')->findOrFail($id);

        return \View::make('provbase::Radius.Check.show',
            $this->compact_prep_view(compact('view_header', 'headline', 'check'))
        );
    }
}
