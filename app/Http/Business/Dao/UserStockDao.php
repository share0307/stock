<?php

namespace App\Http\Business\Dao;

use App\Exceptions\JsonException;
use App\Http\Common\Helper;
use App\Http\Common\Stock;
use App\Model\UserStockAccount;
use App\Model\UserStockLog;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

/**
 * Class StockUserDao
 * 股票用户数据操作类
 * @package App\Http\Business\Dao
 */
class UserStockDao extends DaoBase{
    
    
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
    
    
    /**
     * 用户详情
     * @author  jianwei
     * @param $sentence 语句
     * @param   $select_columns array   查询字段
     * @param $relatives    array   关联关系
     */
    public function getUserStockAccount($account, array $select_columns = ['*'], array $relatives = [])
    {
        //参数错误
        if (!$account || !(is_numeric($account) || is_array($account) || ($account instanceof  UserStockAccount))) {
            throw new JsonException(10000);
        }
        
        $validator_data = $account;
        if ($validator_data instanceof UserStockAccount) {
            $validator_data = $account->toArray();
        } elseif (!is_array($validator_data)) {
            $validator_data = [];
            $validator_data['id'] = $account;
        }
        $validator = Validator::make($validator_data, array(
            'id' => 'required|integer|min:1',
        ));
        if ($validator->fails()) {
            throw new JsonException(10000);
        }
        if (!($account instanceof UserStockAccount)) {
            $account = App::make('UserStockAccountModel')->select($select_columns)->find($validator_data['id']);
        }
        
        if (!$account) {
            throw new JsonException(30000);
        }
        
        if (!empty($relatives)) {
            $account->load($relatives);
        }
        
        return $account;
    }
    
    
    /**
     * 通过用户 id 查询到用户的股票账户信息
     * @author  jianwei
     * @param $user_id  int 用户 id
     * @param $select_columns   array   查询字段
     * @param   $relatives  array   关联关系
     */
    public function getUserAccountByUid($user_account, array $select_columns = ['*'], array $relatives= [])
    {
        //参数错误
        if (!$user_account || !(is_numeric($user_account) || is_array($user_account) || ($user_account instanceof  UserStockAccount))) {
            throw new JsonException(10000);
        }
    
        $validator_data = $user_account;
        if ($validator_data instanceof UserStockAccount) {
            $validator_data = $user_account->toArray();
        } elseif (!is_array($validator_data)) {
            $validator_data = [];
            $validator_data['user_id'] = $user_account;
        }
        $validator = Validator::make($validator_data, array(
            'user_id' => 'required|integer|min:1',
        ));
        if ($validator->fails()) {
            throw new JsonException(10000);
        }
        if (!($user_account instanceof UserStockAccount)) {
            $user_account = App::make('UserStockAccountModel')->select($select_columns)->UserIdQuery($validator_data['user_id'])->orderBy('id','desc')->first();
        }
    
        if (!$user_account) {
            throw new JsonException(30000);
        }
    
        if (!empty($relatives)) {
            $user_account->load($relatives);
        }
    
        return $user_account;
    }
    
    /**
     * 记录用户操作
     * @author  jianwei
     * @param $param    array   记录的参数
     */
    public function logUserHandleStock(array $param)
    {
        $allow_type = config('site.stock.type');
        $rule = array(
            'user_id' =>  ['required','integer',],
            'stock_code'    =>  ['required','string','min:0'],
            'type'  =>  ['required','in:'.implode(',',$allow_type),],
            'stock_price'   =>  ['required','numeric','min:0'],
            'quantity'   =>  ['required','integer','min:0'],
            'stamp_duty_money'  =>  ['required','numeric','min:0',],
            'transfer_fee_money'  =>  ['required','numeric','min:0',],
            'commission_money'  =>  ['required','numeric','min:0',],
            'final_buy_money'  =>  ['required','numeric','min:0',],
        );
        
        $validate = Validator::make($param,$rule);
        
        if($validate->fails()){
            throw new JsonException(10000,$validate->messages());
        }
        
        //校验数量是否有问题
        Stock::CheckStockBuyNumber($param['quantity']);
        
        $UserStockLogModel = app('UserStockLogModel');
    
        $UserStockLogModel->user_id = $param['user_id'];
        $UserStockLogModel->stock_code = $param['stock_code'];
        $UserStockLogModel->stock_price = $param['stock_price'];
        $UserStockLogModel->type = $param['type'];
        $UserStockLogModel->stock_name = !empty($param['stock_name']) ? $param['stock_name'] : 'gp:'.$param['stock_code'];
        $UserStockLogModel->quantity = $param['quantity'];
    
        $UserStockLogModel->stamp_duty_money = $param['stamp_duty_money'];
        $UserStockLogModel->transfer_fee_money = $param['transfer_fee_money'];
        $UserStockLogModel->commission_money = $param['commission_money'];
        $UserStockLogModel->final_buy_money = $param['final_buy_money'];
        
    
        $UserStockLogModel->addtime = Helper::getNow();
        $UserStockLogModel->last_update_time = Helper::getNow();
    
        $UserStockLogModel->save();
        
        return $UserStockLogModel;
    }
    
    
    /**
     * 用户详情
     * @author  jianwei
     * @param $sentence 语句
     * @param   $select_columns array   查询字段
     * @param $relatives    array   关联关系
     */
    public function getUserStocklog($user_stock_log, array $select_columns = ['*'], array $relatives = [])
    {
        //参数错误
        if (!$user_stock_log || !(is_numeric($user_stock_log) || is_array($user_stock_log) || ($user_stock_log instanceof UserStockLog))) {
            throw new JsonException(10000);
        }
        
        $validator_data = $user_stock_log;
        if ($validator_data instanceof UserStockLog) {
            $validator_data = $user_stock_log->toArray();
        } elseif (!is_array($validator_data)) {
            $validator_data = [];
            $validator_data['id'] = $user_stock_log;
        }
        $validator = Validator::make($validator_data, array(
            'id' => 'required|integer|min:1',
        ));
        if ($validator->fails()) {
            throw new JsonException(10000);
        }
        if (!($user_stock_log instanceof UserStockLog)) {
            $user_stock_log = App::make('UserStockLogModel')->select($select_columns)->find($validator_data['id']);
        }
        
        if (!$user_stock_log) {
            throw new JsonException(30003);
        }
        
        if (!empty($relatives)) {
            $user_stock_log->load($relatives);
        }
        
        return $user_stock_log;
    }
    
    
    /**
     * 设置用户股票交易成功!
     * @author  jianwei
     * @param   $user_stock_log int|array|object
     */
    public function setUserStockLogSuccess($user_stock_log)
    {
        $user_stock_log = $this->getUserStocklog($user_stock_log);
        
        //检查状态是否已经成功
        if($user_stock_log->checkSuccessStatus()){
            throw new JsonException(10001);
        }
        
        //检查是否已经完结
        if($user_stock_log->checkIsFinish()){
            throw new JsonException(10001);
        }
    
        $user_stock_log->status = config('stockdb.user_stock_log.status.success_key.code');
    
        $user_stock_log->last_update_time = Helper::getNow();
        
        $user_stock_log->save();
        
        return $user_stock_log;
    }
    
    
    /**
     * 设置用户股票交易完结!
     * @author  jianwei
     * @param   $user_stock_log int|array|object
     */
    public function setUserStockLogFinished($user_stock_log)
    {
        $user_stock_log = $this->getUserStocklog($user_stock_log);
        
        //检查状态是否已经成功
        if(!$user_stock_log->checkSuccessStatus()){
            throw new JsonException(10001);
        }
        
        //检查是否已经完结
        if($user_stock_log->checkIsFinish()){
            throw new JsonException(10001);
        }
        
        $user_stock_log->is_finish = config('stockdb.user_stock_log.is_finish.yes_key.code');
        
        $user_stock_log->finished_time = Helper::getNow();
        
        $user_stock_log->save();
        
        return $user_stock_log;
    }
    
    /**
     * 用户骨牌哦账户余额变化
     * @author  jianwei
     * @param $user_account
     * @param   $money
     */
    public function modifyUserStockBalance($user_account, $money)
    {
        $user_account = $this->getUserAccountByUid($user_account);
    
        $user_account->balance = $user_account->balance + $money;
        
        if($user_account->balance < 0){
            throw new JsonException(10001);
        }
    
        $user_account->save();
        
        return $user_account;
    }
    
    /**
     * 获取用户今天购买某个股票的数量
     * @author  jianwei
     * @param   $user_id    int 用户id
     * @param   $stock_code string  股票
     */
    public function getUserBuyStockTotal($user_id, $stock_code)
    {
        if(
            !is_numeric($user_id) || $user_id < 1 ||
            !is_string($stock_code) || empty($stock_code)
        ){
            throw new JsonException(10000);
        }
    
        $UserStockLogModel = app('UserStockLogModel');
    
        $obj = $UserStockLogModel->UserIdQuery($user_id);
    
        $obj->StockCodeQuery($stock_code);
        
        //开始时间
        $start_time = str_pad(date('Ymd',time()),14,0);
        $obj->StartTimeQuery($start_time);
        
        $end_time = str_pad(date('Ymd',strtotime('+1 day')),14,0);
        $obj->EndTimeQuery($end_time);
        
        //都是已经完成的
        $obj->SuccessstatusQuery();
        
        //查询总数
        $total = $obj->sum('quantity');
        
        return $total;
    }
    
}
