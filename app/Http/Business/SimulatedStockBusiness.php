<?php

namespace App\Http\Business;

use App\Exceptions\JsonException;
use App\Http\Business\Dao\RelationRoomWaterUserDao;
use App\Http\Business\Dao\SimulatedStockDao;
use App\Http\Business\Dao\UserStockDao;
use App\Http\Common\Helper;
use App\Http\Common\Stock;

class SimulatedStockBusiness extends BusinessBase
{
    
    //模拟炒股dao
    private $simulated_stock_dao = null;
    
    //用户股票dao
    private $user_stock_dao = null;
    
    /**
     * 构造方法
     * @author  jianwei
     */
    function __construct(SimulatedStockDao $simulated_stock_dao, UserStockDao $user_stock_dao)
    {
        $this->simulated_stock_dao = $simulated_stock_dao;
        $this->user_stock_dao = $user_stock_dao;
    }
    
    
    /**
     * 股票操作
     * @author  jianwei
     * @param   $user_id    int 用户id
     * @param   $stock_code string  股票代码
     * @param   $quantity   int 数量
     * @param   $stock_price    float   股票价格
     * @param   $type   enum    string  in:买入,out:卖出
     * @notice：此处只是作为 demo 使用
     */
    public function userHandleStock($user_id, $stock_code, $quantity, $stock_price, $type)
    {
        $allow_type = config('site.stock.type');
        if(!is_numeric($user_id) || $user_id < 1 ||
            !is_string($stock_code) || empty($stock_code) ||
            !is_numeric($quantity) || $quantity < 1 ||
            !in_array($type,$allow_type) ||
            !is_numeric($stock_price) || $stock_price < 0
        ){
            throw new JsonException(10000);
        }
        
        //先判断用户是否已经有股票的账户了
        $user_stock_account = $this->user_stock_dao->getUserAccountByUid($user_id);
        
        $handle_response = null;
        //检查此用户是否有符合条件购买此股票
        if($type == $allow_type['stock_in']) {
            $handle_response = $this->userBuyStock($user_stock_account, $stock_code, $quantity, $stock_price);
        }else if($type == $allow_type['stock_out']) {
            $handle_response = $this->userSellStock($user_stock_account, $stock_code, $quantity, $stock_price);
        }
        
        return $handle_response;
    }
    
    
    /**
     * 用户购买股票
     * @author  jianwei
     * @param   $user_account   int|array|object 用户股票账户
     * @param   $stock_code string 股票代码
     * @param   $stock_price    numeric 股票价格
     * @param   $quantity   int 购买的数量
     */
    public function userSellStock($user_account, $stock_code, $quantity, $stock_price)
    {
        if(
            !is_string($stock_code) || empty($stock_code) ||
            !is_numeric($quantity) || $quantity < 1 ||
            !is_numeric($stock_price) || $stock_price < 0
        ){
            throw new JsonException(10000);
        }
        
        $user_account = $this->user_stock_dao->getUserAccountByUid($user_account);
            
        //检查用户是否有足够的股票可售卖
        $stock_calculate = $this->checkUserSellStockPrerequisite($user_account->user_id, $stock_code, $stock_price, $quantity);
        
        //记录一条数据
        $log_param = array();
        $log_param['user_id'] = $user_account->user_id;
        $log_param['stock_code'] = $stock_code;
        $log_param['stock_price'] = $stock_price;
        $log_param['type'] = config('site.stock.type.stock_out');
        $log_param['quantity'] = $quantity;
        $log_param['stamp_duty_money'] = $stock_calculate['stamp_duty_money'];
        $log_param['transfer_fee_money'] = $stock_calculate['transfer_fee_money'];
        $log_param['commission_money'] = $stock_calculate['commission_money'];
        $log_param['final_buy_money'] = $stock_calculate['final_buy_money'];
        
        //记录数据
        $log_response = $this->user_stock_dao->logUserHandleStock($log_param);
        
        //当为购买的时候，先从余额中扣除相应的金额
        //$this->user_stock_dao->modifyUserStockBalance($user_account, -$log_response->final_buy_money);
        
        //先设置日志 status = 1;
        $this->user_stock_dao->setUserStockLogSuccess($log_response->id);
        
        //当是交易成功，那么重新计算股票的成本
        $calculate_user_stock = $this->CalculateUserStock($user_account->user_id, $log_response->id);
        
        //重新计算用户的账户余额
        $calculate_user_balance = $this->CalculateUserBalance($user_account->user_id, $log_response->id);
        
        return $calculate_user_balance;
    }
    
    /**
     * 检查用户是否可以售卖多少股票
     * @author  jianwei
     * @param $user_id  int 用户 id
     * @param $stock_code   string  股票
     * @param   $quantity   init    卖出的股票数量
     */
    public function checkUserSellStockPrerequisite($user_id, $stock_code, $stock_price, $quantity)
    {
        if(
            !is_numeric($user_id) || $user_id < 1 ||
            !is_string($stock_code) || empty($stock_code) ||
            !is_numeric($stock_price) || $stock_price < 0 ||
            !is_numeric($quantity) || $quantity < 1
        ){
            throw new JsonException(10000);
        }
        $user_stock_info = $this->user_stock_dao->getUserStockInfo($user_id, $stock_code);
            
        if(!$user_stock_info->checkStockQuantity($quantity)){
            throw new JsonException(30004);
        }
        
        //获取今天购买的股票数量
        $total_buy_stock_quantity = $this->user_stock_dao->getUserBuyStockTotal($user_id,$stock_code);
        
        //今天可卖股票数量
        $can_sell_stock_quantity = $user_stock_info->quantity - $total_buy_stock_quantity;
        
        if($can_sell_stock_quantity < $quantity){
            throw new JsonException(30005);
        }
        
        
        $stock_calculate = Stock::BuyStockCalculateByNumber($stock_code,$quantity,$stock_price);
        
        return $stock_calculate;
    }
    
    /**
     * 用户购买股票
     * @author  jianwei
     * @param   $user_account   int|array|object 用户股票账户
     * @param   $stock_code string 股票代码
     * @param   $stock_price    numeric 股票价格
     * @param   $quantity   int 购买的数量
     */
    public function userBuyStock($user_account, $stock_code, $quantity, $stock_price)
    {
        if(
            !is_string($stock_code) || empty($stock_code) ||
            !is_numeric($quantity) || $quantity < 1 ||
            !is_numeric($stock_price) || $stock_price < 0
        ){
            throw new JsonException(10000);
        }
    
        $user_account = $this->user_stock_dao->getUserAccountByUid($user_account);
    
        $stock_calculate = $this->checkUserBuyStockPrerequisite($user_account, $stock_code, $stock_price, $quantity);
    
        //记录一条数据
        $log_param = array();
        $log_param['user_id'] = $user_account->user_id;
        $log_param['stock_code'] = $stock_code;
        $log_param['stock_price'] = $stock_price;
        $log_param['type'] = config('site.stock.type.stock_in');
        $log_param['quantity'] = $quantity;
        $log_param['stamp_duty_money'] = $stock_calculate['stamp_duty_money'];
        $log_param['transfer_fee_money'] = $stock_calculate['transfer_fee_money'];
        $log_param['commission_money'] = $stock_calculate['commission_money'];
        $log_param['final_buy_money'] = $stock_calculate['final_buy_money'];
    
        //记录数据
        $log_response = $this->user_stock_dao->logUserHandleStock($log_param);
        
        //当为购买的时候，先从余额中扣除相应的金额
        $this->user_stock_dao->modifyUserStockBalance($user_account, -$log_response->final_buy_money);
        
        //先设置日志 status = 1;
        $this->user_stock_dao->setUserStockLogSuccess($log_response->id);
        
        //当是交易成功，那么重新计算股票的成本
        $calculate_user_stock = $this->CalculateUserStock($user_account->user_id, $log_response->id);
        
        //重新计算用户的账户余额
        $calculate_user_balance = $this->CalculateUserBalance($user_account->user_id, $log_response->id);
        
        return $calculate_user_balance;
    }
    
    /**
     * 计算用户的账户余额
     * @author  jianwei
     * @param   $user_account   int|array|object 用户股票账户
     * @param   $user_stock_log   in|array|object 交易记录
     *
     */
    public function CalculateUserBalance($user_account, $user_stock_log)
    {
        //用户股票账户
        $user_account = $this->user_stock_dao->getUserAccountByUid($user_account);
        
        //用户股票交易记录
        $user_stock_log = $this->user_stock_dao->getUserStocklog($user_stock_log);
        
        //检查状态是否已经成功
        if(!$user_stock_log->checkSuccessStatus()){
            throw new JsonException(10001);
        }
        
        //检查是否已经完结
        if($user_stock_log->checkIsFinish()){
            throw new JsonException(10001);
        }
        
        if($user_stock_log->checkBuyIn()){
            //当为买入，那么先从余额中扣除相应的金额,但是是预先扣除了，所以此处不必要再扣除
            
        }else if($user_stock_log->checkSellOut()){
            //当为卖出，那么把金额加到总金额中
            $user_account->balance = $user_account->balance + $user_stock_log->final_buy_money;
        }else{
            throw new JsonException(10001);
        }
    
        $user_account->last_update_time = Helper::getNow();
        //先保存一下吧..
        $user_account->save();
        
        //把记录设置为已完结
        $this->user_stock_dao->setUserStockLogFinished($user_stock_log);
        
        return $user_account;
    }
    
    /**
     * 检查用户是否符合条件购买某个股票
     * @author  jianwei
     * @param   $user_account   int|array|object 用户股票账户
     * @param   $stock_code string 股票代码
     * @param   $stock_price    numeric 股票价格
     * @param   $quantity   int 购买的数量
     */
    public function checkUserBuyStockPrerequisite($user_account, $stock_code, $stock_price, $quantity)
    {
        if(
            !is_string($stock_code) || empty($stock_code) ||
            !is_numeric($quantity) || $quantity < 1 ||
            !is_numeric($stock_price) || $stock_price < 0
        ){
            throw new JsonException(10000);
        }
        
        $user_account = $this->user_stock_dao->getUserAccountByUid($user_account);
            
        //计算购买这些股票需要的总金额
        $stock_calculate = Stock::BuyStockCalculateByNumber($stock_code,$quantity,$stock_price);
        
        if($stock_calculate['final_buy_money'] > $user_account->balance){
            throw new JsonException(30002);
        }
            
        return $stock_calculate;
    }
    
    
    /**
     * 重新计算用户该股票的成本价跟数量
     * @author  jianwei
     */
    public function CalculateUserStock($user_id, $user_stock_log)
    {
        if(!is_numeric($user_id) || $user_id < 1){
            throw new JsonException(10000);
        }
        
        //获取交易记录
        $user_stock_log = $this->user_stock_dao->getUserStocklog($user_stock_log);
        
        if($user_stock_log->user_id != $user_id){
            throw new JsonException(10000);
        }
        
        //检查状态是否已经成功
        if(!$user_stock_log->checkSuccessStatus()){
            throw new JsonException(10001);
        }
    
        //检查是否已经完结
        if($user_stock_log->checkIsFinish()){
            throw new JsonException(10001);
        }
    
        //原来的数量
        $old_quantity = 0;
        //原来的成本
        $old_cost_price = 0.00;
        
        try {
            //通过股票代码查询出是否已经购入了这个股票
            $user_stock_info = $this->user_stock_dao->getUserStockInfo($user_id, $user_stock_log->stock_code);
            $old_quantity = $user_stock_info->quantity;
            $old_cost_price = $user_stock_info->cost_price;
        }catch (JsonException $e){
            //不做任何操作
            $user_stock_info = app('UserStockListModel');
            $user_stock_info->addtime = Helper::getNow();
    
            $user_stock_info->stock_code = $user_stock_log->stock_code;
            $user_stock_info->stock_name = $user_stock_log->stock_name;
            $user_stock_info->user_id = $user_id;
        }
    
        if($user_stock_log->checkBuyIn()) {
            //当为买入
            //计算数量,简单的相加
            $new_quantity = $old_quantity + $user_stock_log->quantity;
            
            //暂时先做个跳跃吧..
            if($new_quantity == 0){
                //价格不变吧..
                $new_cost_price = $old_cost_price;
                goto new_quantity_zero;
            }
            
            //计算新的成本价，新的成本价 = (旧总价 + 新总价) / 新总量
            $new_cost_price = (($old_cost_price * $old_quantity) + ($user_stock_log->stock_price * $user_stock_log->quantity)) / $new_quantity;
        }elseif ($user_stock_log->checkSellOut()){
            //当为卖出
            //计算数量,简单的相加
            $new_quantity = $old_quantity - $user_stock_log->quantity;
    
            //暂时先做个跳跃吧..
            if($new_quantity == 0){
                //价格不变吧..
                $new_cost_price = $old_cost_price;
                goto new_quantity_zero;
            }
            
            //计算新的成本价，新的成本价 = (旧总价 - 新总价) / 新总量
            $new_cost_price = (($old_cost_price * $old_quantity) - ($user_stock_log->stock_price * $user_stock_log->quantity)) / $new_quantity;
        }else{
            throw new JsonException(10000);
        }
        
        if($new_quantity < 0 || $new_cost_price < 0){
            //少于0，明显出错了!
            throw new JsonException(10001);
        }
    
        new_quantity_zero:
        
        $new_cost_price = Helper::sprint2f(round($new_cost_price,2));
            
        $user_stock_info->cost_price = $new_cost_price;
        $user_stock_info->quantity = $new_quantity;
        $user_stock_info->last_update_time = Helper::getNow();
    
        //保存
        $user_stock_info->save();
    
        if($new_quantity == 0){
            //已经全部抛出去了，所以再次把它删除掉(归档)
            $user_stock_info->delete();
        }
        
        return $user_stock_info;
    }
    
}
