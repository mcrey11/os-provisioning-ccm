<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class RadUserGroupController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS User Groups';
        $headline = '';

        $query = DB::connection('pgsql-radius')->table('radusergroup');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('groupname', 'ilike', "%{$search}%");
            });
        }

        $usergroups = $query->orderByDesc('id')->paginate(25);

        return \View::make('provbase::Radius.UserGroup.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'usergroups'))
        );
    }
}
