<?php

namespace App\Http\Controllers;

use App\Common\codes\CommonCode;
use App\Common\helpers\Encryot;
use App\Model\AccessService;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function sign(Request $request)
    {
    	$params = $request->input();
        $params['timestamp'] = time();

		CommonCode::code(CommonCode::NOT_FOUND)->args()->response(true);

        dd($params);
    }
}
