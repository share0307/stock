<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserStockList
 *  用户现所持有的股票表模型
 * @author  jianwei
 * @package App\Model
 */
class UserStockList extends Model
{
    //使用软删除
    use SoftDeletes;

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'stk_user_stock_list';
    
    
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
     * 开始时间查询
     * @author  jianwei
     */
    public function scopeStartTimeQuery($query, $start_time)
    {
        return $query->where('last_update_time','>=',$start_time);
    }
    
    /**
     * 技术时间查询
     * @author  jianwei
     */
    public function scopeEndTimeQuery($query, $end_time)
    {
        return $query->where('last_update_time','<=',$end_time);
    }
    
    
    /**
     * 判断是否有足够的股票
     * @author  jianwei
     */
    public function checkStockQuantity($stock_quantity)
    {
        return $this->quantity >= $stock_quantity;
    }
    
}
