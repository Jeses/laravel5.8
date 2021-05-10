<?php

namespace App\Exceptions;

use App\Common\codes\CommonCode;
use App\Common\helpers\BLogger;
use App\Common\helpers\Utils;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
	protected $logId = '';
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
	 * @throws Exception
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        //parent::report($exception);
		//避免重复记录日志、区分异常或终止
		$exceptionMsg = $exception->getMessage();
		if(!empty($exceptionMsg)){
			if ($this->logId != Utils::requestId()) {
				//记录日志
				$this->logId = Utils::requestId();
				$excErr['message'] = $exception->getMessage();
				$excErr['line'] = $exception->getLine();
				$excErr['file'] = $exception->getFile();
				BLogger::warning('Exception', CommonCode::OTHER_ERROR, request()->all(), $excErr);
			}

			if (env('APP_ENV') == 'dev' || !empty(request()->input('isDebug'))) {
				$message = $exception->getMessage() . ' on Line:' . $exception->getLine() . ' of file:' . $exception->getFile();
				CommonCode::code(CommonCode::OTHER_ERROR)->message($message)->response(true);
			}

			//避免服务器异常导致客户端崩溃
			CommonCode::code(CommonCode::OTHER_ERROR)->response(true);
		}else{
			parent::report($exception);
		}
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
