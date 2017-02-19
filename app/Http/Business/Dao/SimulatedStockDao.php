<?php

namespace App\Http\Business\Dao;

use App\Exceptions\JsonException;

class SimulatedStockDao extends DaoBase
{
 
    /**
     * 通过用户以及股票代码获取用户购买的该股票的信息
     * @author  jianwei
     * @param   $user_id    int 用户 id
     * @param   $stock_code string  股票代码
     * @param   $select_columns array   查询字段
     * @param   $relatives  array   关联关系
     */
    public function getUserStockInfo($user_id, $stock_code, array $select_columns = ['*'], array $relatives = [])
    {
        if(!is_numeric($user_id) || $user_id < 1 ||
            !is_string($stock_code) || empty($stock_code)
        ){
            throw new JsonException(10000);
        }
        
        $UserStockListModel = app('UserStockListModel');
    
        $usl_obj = $UserStockListModel->select($select_columns);
    
        $usl_obj->UserIdQuery($user_id);
    
        $usl_obj->StockCodeQuery($stock_code);
    
        $usl_obj->orderBy('id','desc');
    
        $usl = $usl_obj->first();
        
        if(empty($usl)){
            throw new JsonException(30001);
        }
        
        if(!empty($relatives)){
            $usl->load($relatives);
        }
        
        return $usl;
    }
    
    
}
