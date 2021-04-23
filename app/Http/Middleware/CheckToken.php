<?php

namespace App\Http\Middleware;

use App\Common\codes\CommonCode;
use App\Common\helpers\Encryot;
use Closure;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $params = $request->input();
        if (!isset($params['stsToken']) || empty($params['stsToken']))
            CommonCode::code(CommonCode::UNAUTHORIZED)->args()->response(true);

        //验证Token是否有效
        Encryot::verifyToken($params['stsToken']);

        //解析出appKey并写入request中
        $explode = explode('|', base64_decode($params['stsToken']));
        $request->offsetSet('appKey', $explode[0]);

        return $next($request);
    }
}
