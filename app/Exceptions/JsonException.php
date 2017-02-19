<?php

namespace App\Exceptions;

use Exception;

class JsonException extends Exception 
{
    /**
     * 错误码列表
     * 10000 - 19999 基本错误
     * 20000 - 29999 股票错误
     * 30000 - 39999 用户相关错误!
     */
    private $code_list = [
        /*---股票错误 start-----*/
        20000    =>  [
            'msg'   =>  '购买股票数量必须大于100且须为100的倍数!'
        ],
        20001    =>  [
            'msg'   =>  '没找到该股票所属板块!'
        ],
        20002   =>  [
           'msg'    =>  '获取股票信息失败！'
        ],
        /*---股票错误 end-----*/
        
        /*---用户错误 start-----*/
        30000   =>  [
            'msg'   =>  '未找到该用户的股票账户！'
        ],
        30001   =>  [
            'msg'   =>  '该用户仍未持有该股票!'
        ],
        30002   =>  [
            'msg'   =>  '用户余额不足以购买此股票！'
        ],
        30003   =>  [
            'msg'   =>  '未找到任何交易记录!'
        ],
        /*---用户错误 end-----*/
    
        /*---基本错误 start-----*/
        //...
        10000 => [
            'msg'    =>  '参数错误!'
        ],
        10001 => [
            'msg'    =>  '系统错误!'
        ],
        /*---基本错误 end-----*/
    ];


    /**
     * 构造函数
     */
    public function __construct($code, $data = [])
    {
        $this->code = $code;
        $this->data = $data;
    }


    /**
     * 获取错误信息
     */
    public function getErrorMsg()
    {
        $re = [
            'code' => 10000,
            'msg'  => $this->code_list[10000]['msg'],
            'data' => '',
            'module'    =>  'simulated.stock',
        ];
        if (empty($this->code_list[$this->code])) {
            return $re;
        }

        $re['code'] = $this->code;
        $re['msg']  = $this->code_list[$this->code]['msg'];
        $re['data'] = $this->data;

        return $re;
    }
}
