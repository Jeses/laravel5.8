<?php

namespace App\Http\Middleware;

use App\Common\helpers\BLogger;
use App\Common\helpers\Utils;
use Closure;
use Illuminate\Http\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class RequestLog
{
	protected $code;
	protected $sequence;

	/**
	 * Handle an incoming request.
	 * @param         $request
	 * @param Closure $next
	 * @return mixed
	 * @throws \Exception
	 * @Date  : 2021/3/12 14:39
	 * @Author:青山
	 * @Email :<yz_luck@163.com>
	 */
	public function handle($request, Closure $next)
	{
		$startTime = microtime(true);

		$response = $next($request);
		$output = $response->getContent();

		$runTime = sprintf("%.3f", microtime(true) - $startTime);
		$output = json_decode($output, true);
		$output['takeTime'] = $runTime . ' ms';

		BLogger::info('Request', $request->input(), $output);

		return $response;
	}
}
