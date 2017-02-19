<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserStockLog
 * 用户操作记录记录
 * @author  jianwei
 * @package App\Model
 */
class UserStockLog extends Model
{
    //使用软删除
    use SoftDeletes;

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'stk_user_stock_log';
    
    
    /**
     * 通过用户 id 查询
     * @author  jianwei
     */
    public function scopeUserIdQuery($query, $user_id)
    {
        return $query->where('user_id',$user_id);
    }
    
    
    /**
     * 通过股票代码查询
     * @author  jianwei
     */
    public function scopeStockCodeQuery($query, $stock_code)
    {
        return $query->where('stock_code', $stock_code);
    }
    
    /**
     * 判断是否买入
     * @author  jianwei
     */
    public function checkBuyIn()
    {
        return $this->type == config('stockdb.user_stock_log.type.in_key.code');
    }
    
    /**
     * 判断是否卖出
     * @author  jianwei
     */
    public function checkSellOut()
    {
        return $this->type == config('stockdb.user_stock_log.type.out_key.code');
    }
    
    
}
