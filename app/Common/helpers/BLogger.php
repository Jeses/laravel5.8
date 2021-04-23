<?php
/**
 * Created by PhpStorm.
 * @Date  : 2021/3/12 17:02
 * @Author:青山
 * @Email :<yz_luck@163.com>
 */

namespace App\Common\helpers;

use App\Common\codes\CommonCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Monolog\Handler\StreamHandler;

class BLogger extends Facade
{
	private static $logFile = 'logs/';

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'BLogger';
	}

	/**
	 * 打印info日志
	 * @param string       $desc   描述
	 * @param string|array $params 传递的参数
	 * @param string|array $ret    返回的结果
	 * @throws \Exception
	 * @Date  : 2021/3/12 17:58
	 */
	public static function info($desc = '', $params = '', $ret = '')
	{
		$appName = env('APP_NAME') ?? 'log';
		$logFile = self::$logFile . $appName . '.' . date('Y-m-d-H') . '.log';
		$logInfo = self::getStrLog($desc, $params, $ret);

		self::writeLog('[INFO]', $logFile, $logInfo);
	}

	/**
	 * 打印warning日志
	 * @param string       $desc   描述
	 * @param string|array $params 传递的参数
	 * @param string|array $ret    返回的结果
	 * @param int          $errNo  错误码
	 * @throws \Exception
	 * @Date  : 2021/3/12 18:25
	 */
	public static function warning($desc = '', $errNo = 9999, $params = '', $ret = '')
	{
		$appName = env('APP_NAME') ?? 'log';
		$logFile = self::$logFile . $appName . '.' . date('Y-m-d-H') . '.wf.log';
		$errMsg = CommonCode::code($errNo)->getMessage();
		$logInfo = self::getStrLog($desc, $params, $ret, $errMsg);
		self::writeLog('[WARNING]', $logFile, $logInfo);
	}

	/**
	 * 打印debug日志
	 * @param string       $desc   描述
	 * @param string|array $params 传递的参数
	 * @param string|array $ret    返回的结果
	 * @throws \Exception
	 * @Date  : 2021/3/12 18:21
	 */
	public static function debug($desc = '', $params = '', $ret = '')
	{
		$appName = env('APP_NAME') ?? 'log';
		$logFile = self::$logFile . $appName . '.' . date('Y-m-d-H') . '.debug.log';
		$logInfo = self::getStrLog($desc, $params, $ret);
		self::writeLog('[DEBUG]', $logFile, $logInfo);
	}

	/**
	 * 写入日志
	 * @param string $name
	 * @param string $logFile
	 * @param string $logInfo
	 * @throws \Exception
	 * @Date  : 2021/3/12 18:42
	 */
	private static function writeLog($name = '[INFO]', $logFile = '', $logInfo = '')
	{
		$logModel = new \Monolog\Logger($name);
		$logModel->pushHandler(new StreamHandler(storage_path($logFile), 200));
		$logModel->info($logInfo);
	}

	/**
	 * 获取log字符串
	 * @param string       $desc   描述
	 * @param string|array $params 传递的参数
	 * @param string|array $ret    返回的结果
	 * @param string       $errMsg 错误信息
	 * @return string
	 * @throws \Exception
	 * @Date  : 2021/3/12 18:16
	 */
	private static function getStrLog($desc, $params, $ret, $errMsg = '')
	{
		$log = array();

		$log[] = '[' . (new \DateTime())->format('Y-m-d H:i:s') . ']';
		$log[] = 'logId[' . Utils::requestId().']';
		$ip = Utils::getRealIp() ?? \request()->getClientIp() ?? '-';
		$log[] = 'IP[' . $ip.']';
		//$log[] = 'Cookie:' . \request()->cookie('cookieName');

		$log[] = "errMsg[$errMsg]";

		//$uri = self::getUri();
		$uri = \request()->getRequestUri();
		$log[] = "desc[$uri $desc]";

		$params = self::jsonEncode($params);
		$log[] = "params[$params]";

		$ret = self::jsonEncode($ret);
		$log[] = "ret[$ret]";

		return implode(' ', $log);
	}

	/**
	 * 转换为json字符串，防止中文被编码的问题（仅限utf-8编码格式）
	 *
	 * @param mixed $param
	 * @return string
	 *
	 */
	public static function jsonEncode($param)
	{
		if (version_compare(PHP_VERSION, '5.4.0', '<')) {
			return urldecode(json_encode(self::_urlEncode($param)));
		}
		return json_encode($param, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * 自定义urlencode函数，仅对中文进行urlencode编码（仅限utf-8编码格式）
	 * @param mixed $param
	 * @return string
	 */
	private static function _urlEncode($param)
	{
		if (is_array($param)) {
			foreach ($param as $key => $value) {
				$param[$key] = self::_urlEncode($value);
			}
		} else {
			// 若包含中文，则进行urlencode编码（仅限utf-8编码格式）
			if (preg_match("/[\x7f-\xff]/", $param)) {
				$param = urlencode($param);
			}
		}
		return $param;
	}

	/**
	 * 获取uri
	 * 例如：uri=/order/driver/detail?order_no=123
	 * 返回：order driver detail
	 *
	 * @return string
	 *
	 */
	private static function getUri()
	{
		$uri = $_SERVER['REQUEST_URI'];
		if (empty($uri)) {
			return '';
		}
		$len = strpos($uri, '?');
		$len > 0 && $uri = substr($uri, 0, $len);
		return trim(str_replace('/', ' ', $uri));
	}
}