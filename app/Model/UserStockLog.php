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
     * 状态完结
     * @author  jianwei
     */
    public function scopeSuccessstatusQuery($query)
    {
        return $query->where('status', config('stockdb.user_stock_log.status.success_key.code'));
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
    
    
    /**
     * 判断是否已经成功
     * @author  jianwei
     */
    public function checkSuccessStatus()
    {
        return $this->status == config('stockdb.user_stock_log.status.success_key.code');
    }
    
    /**
     * 检查是否已经完结
     * @author  jianwei
     */
    public function checkIsFinish()
    {
        return $this->is_finish == config('stockdb.user_stock_log.is_finish.yes_key.code');
    }
    
}
