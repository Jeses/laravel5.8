<?php
/**
 * Created by PhpStorm.
 * @Date  : 2021/3/15 14:07
 * @Author:青山
 * @Email :<yz_luck@163.com>
 */

namespace App\Common\helpers;


use App\Common\codes\CommonCode;

class Encryot
{
    protected static $_durationSeconds = 3600; //有效时间

    /**
     * 创建签名
     * @param array  $params          数据集
     * @param string $AccessKeySecret 秘钥
     * @return string
     * @Date  : 2021/3/15 14:56
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public static function createSign($params, $AccessKeySecret)
    {
        $query = '';
        //先将参数以其参数名的字典序升序进行排序
        ksort($params);

        //遍历排序后的参数数组中的每一个key/value键值对
        foreach ($params as $key => $val) {
            if ($key === 'sign') {
                continue;
            }
            //为key/value对生成一个key=value格式的字符串，并拼接到待签名字符串后面
            $query .= "$key=$val";
        }

        //拼接当前调用服务对应的秘钥
        $query .= $AccessKeySecret;

        //生成32位签名
        return md5($query);
    }

    /**
     * 创建Token
     * @param $appKey
     * @return string
     * @Date  : 2021/3/15 16:24
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public static function createToken($appKey)
    {
        //$appKey、此时的时间戳，并将过期时间拼接在一起
        $appKey = is_array($appKey) ? current($appKey) : $appKey;

        //将用户appKey进行二次加密处理
        $key      = md5($appKey . self::getSystemToken());
        $time     = time();
        $end_time = $time + self::$_durationSeconds;

        //设置token过期时间一小时
        $info = $appKey . '|' . $key . '|' . $time . '|' . $end_time;

        //使用sha1Key对base64EncodedStr进行HMAC-SHA1哈希得到字节数组
        $signature = md5(self::getHmacSha1($info, self::getSystemToken()));

        //拼接 Token字符串
        $result['stsToken'] = base64_encode(($info . '|' . $signature));
        $result['expire']   = self::$_durationSeconds;
        return $result;
    }

    /**
     * 验证Token信息
     * @param string $Token token令牌
     * @Date  : 2021/3/15 16:50
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public static function verifyToken($Token)
    {
        if (!isset($Token) || empty($Token) || !is_string($Token))
            CommonCode::code(CommonCode::PARAM_INVALID)->args('Token')->response(true);

        //处理token数据进行处理
        $explode = explode('|', base64_decode($Token));

        //验证Token是否有效
        if (time() > $explode[3])
            CommonCode::code(CommonCode::TOKEN_ERR_TIME_OUT)->args()->response(true);

        //从新计算Token
        $info = $explode[0] . '|' . $explode[1] . '|' . $explode[2] . '|' . $explode[3];

        //使用sha1Key对base64EncodedStr进行HMAC-SHA1哈希得到字节数组
        $trueSignature = md5(self::getHmacSha1($info, self::getSystemToken()));

        if (strnatcmp($explode[4], $trueSignature) != 0)
            CommonCode::code(CommonCode::TOKEN_ERR_FAILED)->args()->response(true);
    }

    /**
     * 获取系统令牌
     * @return mixed|string
     * @Date  : 2021/3/15 16:50
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    private static function getSystemToken()
    {
        //获取当前服务APP_TOKEN 如果获取不到取默认TOKEN
        return env('APP_TOKEN') ?? 'UNlUA1xfvXB';
    }

    /**
     * 使用 HMAC-SHA1 签名方法对对encryptText进行签名
     * @param string $encryptText 被签名的字符串
     * @param string $encryptKey  密钥
     * @return false|string
     * @Date  : 2021/3/15 14:42
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    private static function getHmacSha1($encryptText, $encryptKey)
    {
        $signature = "";
        if (function_exists('hash_hmac')) {
            $signature = hash_hmac("sha1", $encryptText, $encryptKey, true);
        } else {
            $blocksize = 64;
            $hashfunc  = 'sha1';
            if (strlen($encryptKey) > $blocksize) {
                $encryptKey = pack('H*', $hashfunc($encryptKey));
            }
            $encryptKey = str_pad($encryptKey, $blocksize, chr(0x00));
            $ipad       = str_repeat(chr(0x36), $blocksize);
            $opad       = str_repeat(chr(0x5c), $blocksize);
            $hmac       = pack(
                'H*', $hashfunc(
                    ($encryptKey ^ $opad) . pack(
                        'H*', $hashfunc(
                            ($encryptKey ^ $ipad) . $encryptText
                        )
                    )
                )
            );
            $signature  = $hmac;
        }
        return $signature;
    }
}