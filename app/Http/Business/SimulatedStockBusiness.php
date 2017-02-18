<?php

namespace App\Http\Business;

use App\Exceptions\JsonException;
use App\Http\Business\Dao\RelationRoomWaterUserDao;
use App\Http\Business\Dao\SimulatedStockDao;

class SimulatedStockBusiness extends BusinessBase
{
    
    //模拟炒股dao
    private $simulated_stock_dao = null;
    
    //用户股票dao
    private $stock_user_dao
    
    /**
     * 构造方法
     * @author  jianwei
     */
    function __construct(SimulatedStockDao $simulated_stock_dao)
    {
        $this->simulated_stock_dao = $simulated_stock_dao;
    }
    
    
    /**
     * 股票操作
     * @author  jianwei
     * @param   $user_id    int 用户id
     * @param   $stock_code string  股票代码
     * @param   $quantity   int 数量
     * @param   $stock_price    float   股票价格
     */
    public function userHandleStock($user_id, $stock_code, $quantity, $stock_price)
    {
        if(!is_numeric($user_id) || $user_id < 1 ||
            !is_string($stock_code) || empty($stock_code) ||
            !is_numeric($quantity) || $quantity < 1 ||
            !is_numeric($stock_code) || $stock_code < 0){
            throw new JsonException(10000);
        }
        
        //先判断用户是否已经有股票的账户了
//        $user_stock_account =
        
        
    }
    
}
