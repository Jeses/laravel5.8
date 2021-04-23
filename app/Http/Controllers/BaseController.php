<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{

    public function __construct(Request $request)
    {
        /**
         * 检测Token是否有效
         */
        if (env('APP_ENV') == 'production') {
            //$this->middleware('checkToken');
        }
    }
}
