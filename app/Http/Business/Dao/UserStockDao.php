<?php

namespace App\Http\Business\Dao;

use App\Exceptions\JsonException;
use App\Model\UserStockAccount;

/**
 * Class StockUserDao
 * 股票用户数据操作类
 * @package App\Http\Business\Dao
 */
class UserStockDao extends DaoBase{
    
    
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
    
    
}
