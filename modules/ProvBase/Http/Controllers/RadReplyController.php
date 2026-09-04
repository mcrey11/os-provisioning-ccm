<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class RadReplyController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS Reply Attributes';
        $headline = '';

        $query = DB::connection('pgsql-radius')->table('radreply');

        if ($search = request('search')) {
            $query->where('username', 'ilike', "%{$search}%");
        }

        $replies = $query->orderByDesc('id')->paginate(25);

        return \View::make('provbase::Radius.Reply.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'replies'))
        );
    }

    public function show($id)
    {
        $view_header = 'Reply Attribute Detail';
        $headline = '';

        $reply = DB::connection('pgsql-radius')->table('radreply')->findOrFail($id);

        return \View::make('provbase::Radius.Reply.show',
            $this->compact_prep_view(compact('view_header', 'headline', 'reply'))
        );
    }
}
