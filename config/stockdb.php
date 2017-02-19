<?php

/**
 * 返回数据库相关字段配置
 * @author  jianwei
 */
return array(
    'user_stock_log'    =>  [
        'status'    =>  [
            'nok_key'   =>  [
                'code'  =>  0,
                'msg'   =>  '初始状态',
            ],
            'success_key'   =>  [
                'code'  =>  1,
                'msg'   =>  '交易成功!',
                
            ],
            'fails_key' =>  [
                'code'  =>  2,
                'msg'   =>  '交易失败!'
            ],
            'cancel_key'    =>  [
                'code'  =>  3,
                'msg'   =>  '取消交易',
            ]
        ],
        'type'  =>  [
            'in_key'    =>  [
                'code'  =>  'in',
                'msg'   =>  '买入',
            ],
            'out_key'   =>  [
                'code'  =>  'out',
                'msg'   =>  '卖出'
            ]
        ],
    ],
);
