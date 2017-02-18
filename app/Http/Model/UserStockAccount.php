<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserStockAccount
 *  用户故事账户表模型
 * @author  jianwei
 * @package App\Model
 */
class UserStockAccount extends Model
{
    //使用软删除
    use SoftDeletes;

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'stk_user_stock_account';
    
    
    
}
