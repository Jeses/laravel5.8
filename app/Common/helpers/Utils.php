<?php
/**
 * Created by PhpStorm.
 * @Date  : 2021/3/12 12:02
 * @Author:青山
 * @Email :<yz_luck@163.com>
 */

namespace App\Common\helpers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class Utils
{
    protected static $_requestId;

    /**
     * 获取requestId
     * 如果请求中携带则使用携带的，如果没有就生成
     *
     * @param null|string $requestId
     * @return string
     */
    public static function requestId($requestId = null)
    {
        if ($requestId) {
            static::$_requestId = $requestId;
        } elseif (!static::$_requestId) {
            $request = new Request();
            if ($request instanceof Request) {
                static::$_requestId = $request->header('X-REQUEST-ID', null);
            }
            if (!static::$_requestId) {
                static::$_requestId = ((microtime(true) * 100000) & 0x7FFFFFFF);
                //static::$_requestId = Uuid::uuid();
            }
        }
        return static::$_requestId;
    }

    /**
     * 获取IP
     * @return string
     */
    public static function getRealIp()
    {
        $ip = FALSE;
        //客户端IP 或 NONE
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        //多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match('/^((192\.168|172\.([1][6-9]|[2]\d|3[01]))(\.([2][0-4]\d|[2][5][0-5]|[01]?\d?\d)){2}|10(\.([2][0-4]\d|[2][5][0-5]|[01]?\d?\d)){3})$/', $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        //客户端IP 或 (最后一个)代理服务器 IP
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    /**
     * 获取url中的域名部分
     *
     * @param string $url
     * @return string
     * @throws \Exception
     */
    public static function getDomain($url)
    {
        $parsedUrl = parse_url($url);
        if (!$parsedUrl) {
            throw new \Exception('invalid url ' . $url);
        }
        $array = explode('.', $parsedUrl['host']);
        $suffix = array_pop($array);
        $main = array_pop($array);
        if (in_array($suffix, ['cn']) && in_array($main, ['com', 'net', 'gov', 'org', 'edu'])) {
            $suffix = $main . '.' . $suffix;
            $main = array_pop($array);
        }
        return $main . '.' . $suffix;
    }

    /**
     * 格式化文件大小显示
     *
     * @param int $size
     * @return string
     */
    public static function formatSize($size)
    {
        $prec = 3;
        $size = round(abs($size));
        $units = array(
            0 => " B ",
            1 => " KB",
            2 => " MB",
            3 => " GB",
            4 => " TB"
        );
        if ($size == 0) {
            return str_repeat(" ", $prec) . "0$units[0]";
        }
        $unit = min(4, floor(log($size) / log(2) / 10));
        $size = $size * pow(2, -10 * $unit);
        $digi = $prec - 1 - floor(log($size) / log(10));
        $size = round($size * pow(10, $digi)) * pow(10, -$digi);

        return $size . $units[$unit];
    }


    /**
     * curl
     *
     * @param string $url
     * @param string|array $vars
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    public static function curl($url, $vars, $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
//        curl_setopt($ch, CURLOPT_USERAGENT, "tuibei-php");
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (strtolower($method) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        }
        $response = curl_exec($ch);
        if ($response === false) {
            throw new \Exception(curl_error($ch));
        }
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 !== $httpStatusCode) {
            throw new \Exception($response, $httpStatusCode);
        }
        curl_close($ch);
        return $response;
    }


    /**
     * 异常转数组
     *
     * @param \Exception $exception
     * @return array
     */
    public static function exceptionToArray($exception)
    {
        $array = [
            'name' => ($exception instanceof \Exception || $exception instanceof \ErrorException) ? $exception->getName() : 'Exception',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        if ($exception instanceof HttpResponseException) {
            $array['status'] = $exception->getCode();
        }
        if (env('APP_ENV') === 'dev') {
            $array['type'] = get_class($exception);
            if (!$exception instanceof \Exception) {
                $array['file'] = $exception->getFile();
                $array['line'] = $exception->getLine();
                $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof \Exception) {
                    $array['error-info'] = $exception->errorInfo;
                }
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = static::exceptionToArray($prev);
        }
        return $array;
    }

    /**
     * 服务器IP
     *
     * @return string
     */
    public static function getServerIp()
    {
        if (!empty($_SERVER['SERVER_ADDR'])) {
            $ip = $_SERVER['SERVER_ADDR'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $ip = gethostbyname($_SERVER['SERVER_NAME']);
        } else {
            // for php-cli(phpunit etc.)
            $ip = defined('PHPUNIT_RUNNING') ? '127.0.0.1' : gethostbyname(gethostname());
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }

    /**
     * 判断移动设备
     *
     * @return bool
     */
    public static function isMobile()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? "");
        $mobileAgents = ['mobile', 'nokia', 'iphone', 'ipad', 'android', 'samsung', 'htc', 'blackberry'];

        return str_replace($mobileAgents, '', $agent) != $agent;
    }

    /**
     *  删除非站内链接
     *
     * @access    public
     * @param     string $body 内容
     * @param     array $allowUrls 允许的超链接
     * @return    string
     */
    public static function replaceLinks(&$body, $allowUrls = [])
    {
        $hostRule = join('|', $allowUrls);
        $hostRule = preg_replace("#[\n\r]#", '', $hostRule);
        $hostRule = str_replace('.', "\\.", $hostRule);
        $hostRule = str_replace('/', "\\/", $hostRule);
        $arr = '';
        preg_match_all("#<a([^>]*)>(.*)<\/a>#iU", $body, $arr);
        if (is_array($arr[0])) {
            $rpArr = [];
            $tgArr = [];
            foreach ($arr[0] as $i => $v) {
                if ($hostRule != '' && preg_match('#' . $hostRule . '#i', $arr[1][$i])) {
                    continue;
                } else {
                    $rpArr[] = $v;
                    $tgArr[] = $arr[2][$i];
                }
            }
            if (!empty($rpArr)) {
                $body = str_replace($rpArr, $tgArr, $body);
            }
        }
        unset($arr, $rpArr, $tgArr);
        return $body;
    }

    /**
     * 判断是否是微信浏览器
     * @return bool
     * @Date  : 2021/3/12 12:07
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public static function isWeChatBrowser()
    {
        return false !== strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger');
    }

    /**
     * 格式化秒数 并且补位
     * @param int $seconds
     * @return string
     * @Date  : 2021/3/12 12:07
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public static function formatSeconds($seconds = 0){
        $minutes = sprintf("%02d", floor(intval($seconds)/60));
        $seconds = sprintf("%02d", ceil(intval($seconds)%60));

        return $minutes . ':' . $seconds;
    }

    /**
     * 转驼峰
     * @param string $str
     * @param string $separator
     * @param bool   $lcFirst
     * @return string|string[]
     * @Date  : 2021/3/12 12:07
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public static function transCamel(string $str, string $separator = '-', bool $lcFirst = false)
    {
        $camelStr = str_replace(' ', '', ucwords(implode(' ', explode($separator, $str))));
        return $lcFirst ? lcfirst($camelStr) : $camelStr;
    }

    /**
     * 把Array的key换成驼峰
     * @param array  $array
     * @param string $separator
     * @param bool   $lcFirst
     * @return array
     * @Date  : 2021/3/12 12:07
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public static function key2Camel(array $array, string $separator = '_', bool $lcFirst = true)
    {
        $values = [];
        foreach ($array as $k => $v) {
            $nk = static::transCamel($k, $separator, $lcFirst);
            $values[$nk] = $v;
        }
        return $values;
    }

    /**
     * 随机字符串
     * @param int  $length
     * @param bool $is_md5
     * @return string
     * @Date  : 2021/3/16 19:34
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public static function getRandStr( $length = 10 , $is_md5 = false)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $chars .= time();
        $string = '';

        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $string .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }

        if($is_md5)
        {
            $string = md5($string);
        }
        return $string;
    }
}