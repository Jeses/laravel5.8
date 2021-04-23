<?php
/**
 * Created by PhpStorm.
 * @Date  : 2021/3/12 11:34
 * @Author:青山
 * @Email :<yz_luck@163.com>
 */

namespace App\Common\traits;


use App\Common\helpers\Utils;
use \Illuminate\Http\Response;

trait ApiCodeTrait
{
    protected static $undefinedErrorMessage = '未定义错误。';

    protected $_code = null;
    protected $_args = [];
    protected $_data = [];

    public function __construct($code)
    {
        if (!is_null($code)) {
            $this->_code = $code;
        }
    }

    /**
     * @param $code
     * @param null $data
     * @return static
     */
    public static function code($code, $data = null)
    {
        $instance = new static($code);
        if ($data) {
            if (is_string($data)) {
                $instance->data(['message' => $data]);
            } elseif (is_array($data)) {
                $instance->data($data);
            }
        }

        return $instance;
    }

    /**
     * 抛异常，抛异常时data会忽略
     * @throws \Exception
     * @Date  : 2021/3/12 11:39
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public function throwException()
    {
        $this->convert();
        throw new \Exception($this->_data['message'], $this->_data['code']);
    }

    /**
     * 返回结果
     * @param bool $end
     * @return Response
     * @Date  : 2021/3/12 11:51
     * @Author:青山
     * @Email :<yz_luck@163.com>
     */
    public function response($end = false)
    {
        $this->convert();
        $response = new Response();

        if ($response instanceof Response) {
            $response->setContent($this->_data);
            if($end){
				$response->throwResponse();
            }else{
                return $response;
            }
        } else {
            echo $this->_data['message'] . "\n";
        }
    }

    /**
     * 获取消息
     * @return string
     */
    public function getMessage()
    {
        $this->convert();
        return $this->_data['message'];
    }

    /**
     * 消息模板参数
     * @param array ...$args
     * @return $this
     */
    public function args(...$args)
    {
        $this->_args = $args;
        return $this;
    }

    /**
     * 指定消息
     * @param $message
     * @return $this
     */
    public function message($message)
    {
        $this->_data['message'] = $message;
        return $this;
    }

    /**
     * 附带数据
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        $this->_data['data'] = array_merge($this->_data, $data);
        return $this;
    }

    protected function convert()
    {
		$this->_data['data'] = [];
		$this->_data['code'] = $this->_code;
		$this->_data['message'] = $this->_data['message'] ?? static::CODE_MESSAGES[$this->_code] ?? static::$undefinedErrorMessage;
        $this->_data['logId'] = Utils::requestId();
        $this->_data['serverTime'] = date('Y-m-d H:i:s',time());

        empty($this->_args) || $this->_data['message'] = call_user_func_array('sprintf', array_merge([$this->_data['message']], $this->_args));
    }
}