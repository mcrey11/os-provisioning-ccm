<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class RadGroupReplyController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS Group Reply Attributes';
        $headline = '';

        $query = DB::connection('pgsql-radius')->table('radgroupreply');

        if ($group = request('group')) {
            $query->where('groupname', 'ilike', "%{$group}%");
        }

        $groupreplies = $query->orderBy('groupname')->orderBy('id')->paginate(25);

        return \View::make('provbase::Radius.GroupReply.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'groupreplies'))
        );
    }
}
