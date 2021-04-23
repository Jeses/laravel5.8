<?php
/**
 * Created by PhpStorm.
 * @Date  : 2021/3/12 11:33
 * @Author:青山
 * @Email :<yz_luck@163.com>
 */

namespace App\Common\codes;


use App\Common\traits\ApiCodeTrait;

class CommonCode
{
    use ApiCodeTrait;

    const SUCCESS = 1; //本项目成功状态1

    const VIDEO_CALL_CLOSE = 8101; //事件被终止

    const TOKEN_ERR_TIME_OUT = 9301; //Token过期
    const TOKEN_ERR_FAILED   = 9303;  //Token异常，非法Token

    const UNAUTHORIZED       = 9401; //缺少用户认证
    const FORBIDDEN          = 9403; //没有权限，禁止
    const NOT_FOUND          = 9404; //找不到
    const METHOD_NOT_ALLOWED = 9405; //请求方法不允许，仅接受POST请求

    const SIGN_ERR_TIME_OUT = 9501;  //Sign过期
    const SIGN_ERR_FAILED   = 9503;  //Sign异常，非法Sign

    const PARAM_MISSING = 9601; //参数缺失
    const PARAM_INVALID = 9602; //参数错误

    const DB_SAVE_ERROR     = 9701; //DB保存错误
    const DB_DATA_EXIST     = 9702; //数据存在
    const DB_DATA_NOT_EXIST = 9703; //数据不存在


    const UPLOAD_ERR_SIZE    = 9802; //文件尺寸超出
    const UPLOAD_ERR_NO_FILE = 9804; //没有文件被上传
    const UPLOAD_ERR_TYPE    = 9811; //文件类型错误
    const UPLOAD_ERR_INVALID = 9812; //无效文件
    const UPLOAD_ERR_OTHER   = 9813; //没有文件被上传

    const EXPORT_DATA_TOO_MUCH = 9902; //数据输出过大

    const OTHER_ERROR = 9999; //其它错误

    const CODE_MESSAGES = [
        self::SUCCESS => 'SUCCESS',

        self::TOKEN_ERR_TIME_OUT => 'Token已失效',
        self::TOKEN_ERR_FAILED   => '验证Token失败',

        self::SIGN_ERR_TIME_OUT => '签名已失效',
        self::SIGN_ERR_FAILED   => '验证签名失败',

        self::UNAUTHORIZED       => '用户未认证',
        self::FORBIDDEN          => '没有权限',
        self::NOT_FOUND          => '找不到该项',
        self::METHOD_NOT_ALLOWED => '接口请求方式有误，仅接受POST请求',

        self::PARAM_MISSING => '「%s」不能为空',
        self::PARAM_INVALID => '「%s」参数无效',
        self::VIDEO_CALL_CLOSE => '「%s」已结束当前事件,无法进行通话邀请',

        self::UPLOAD_ERR_SIZE    => '文件尺寸超出限制',
        self::UPLOAD_ERR_NO_FILE => '没有文件被上传',
        self::UPLOAD_ERR_TYPE    => '文件类型错误',
        self::UPLOAD_ERR_INVALID => '无效文件',
        self::DB_SAVE_ERROR      => '保存出错',
        self::DB_DATA_EXIST      => '数据已存在',
        self::DB_DATA_NOT_EXIST  => '数据不已存在',

        self::EXPORT_DATA_TOO_MUCH => '数据量过大，请联系技术人员~',

        self::OTHER_ERROR => '系统错误',
    ];

}