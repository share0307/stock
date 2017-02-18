<?php

/**
 * 返回股票配置
 */
return array(
    //板块
    'plate' =>  [
        'shzb_key'    =>  [
            'code'  =>  'shzb',
            'msg'   =>  '上海主板市场',
            //过户费
            'transfer_fee'  =>  0.00002,
            //最低收取的过户费
            'min_transfer_fee'  =>  0.6,
            //某些股票接口需要的前缀
            'prefix'    =>  'sh',
        ],
        'szzb_key'   =>  [
            'code'  =>  'szzb',
            'msg'   =>  '深圳主板市场',
            //过户费
            'transfer_fee'  =>  0,
            //最低收取的过户费
            'min_transfer_fee'  =>  0,
            //某些股票接口需要的前缀
            'prefix'    =>  'sz',
        ],
        'szzx_key'  =>  [
            'code'  =>  'szzx',
            'msg'   =>  '深圳中小板',
            //过户费
            'transfer_fee'  =>  0,
            //最低收取的过户费
            'min_transfer_fee'  =>  0,
            //某些股票接口需要的前缀
            'prefix'    =>  'sz',
        ],
        'szcy_key'  =>  [
            'code'  =>  'szcy',
            'msg'   =>  '深圳创业板',
            //过户费
            'transfer_fee'  =>  0,
            //最低收取的过户费
            'min_transfer_fee'  =>  0,
            //某些股票接口需要的前缀
            'prefix'    =>  'sz',
        ],
    ],
    
    //正则，用于判断是上交所还是深交所，还有板块
    /**
     * 判断一个股票是来自哪个板块的
     * @author  jianwei
     * @param $stock    string 股票名称
     * 上海就一个主板市场，股票代码前三位为：600、601、603
     * 深圳有主板，中小板，创业板之分
     * 主板代码前三位：000、001
     * 中小板前三位：002
     * 创业板前三位：300
     */
    'regexp_group'    =>  [
        //上海主板块
        [
            'regexp'    =>  '/^60[0|1|2][\d+]{3}/',
            'plate'       =>  'shzb_key',
        ],
        //深圳主板快
        [
            'regexp'    =>  '/^00[0|1][\d+]{3}/',
            'plate'       =>  'szzb_key',
        ],
        //深圳中小板
        [
            'regexp'    =>  '/^002[\d+]{3}/',
            'plate'       =>  'szzx_key',
        ],
        //深圳创业版
        [
            'regexp'    =>  '/^300[\d+]{3}/',
            'plate'       =>  'szcy_key',
        ],
    ],
    
    //印花税,	0.1%(千分之1)
    'stamp_duty'    =>  0.001,
    //佣金，各营业部有可能不同，一般在0.01~0.03%之间，但先写死 0.03%
    'commission'    =>  0.0003,
    //最低佣金,单位元
    'min_commission'    =>  5,
    
    //每手多少股，也就是最少要买多少股，而股票买卖的单位应该是手
    'board_lot' =>  100,
);
