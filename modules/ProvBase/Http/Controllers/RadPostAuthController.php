<?php

namespace Modules\ProvBase\Http\Controllers;

use App\Http\Controllers\BaseController;
use Modules\ProvBase\Entities\RadPostAuth;

class RadPostAuthController extends BaseController
{
    public function index()
    {
        $view_header = 'RADIUS Auth Log';
        $headline = '';

        $query = RadPostAuth::query();

        if ($search = request('search')) {
            $query->where('username', 'ilike', "%{$search}%");
        }

        if (request('status') === 'success') {
            $query->where('reply', 'Access-Accept');
        } elseif (request('status') === 'fail') {
            $query->where('reply', '!=', 'Access-Accept');
        }

        $auths = $query->orderByDesc('authdate')->paginate(25);

        return \View::make('provbase::Radius.PostAuth.index',
            $this->compact_prep_view(compact('view_header', 'headline', 'auths'))
        );
    }
}
