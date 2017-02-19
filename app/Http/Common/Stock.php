<?php

namespace App\Http\Common;

use App\Exceptions\JsonException;
use App\Http\Business\Api\StockApi;

/**
 * Class Stock
 * 用于股票公式之类的
 * @package App\Http\Common
 */
class Stock
{
    
    /**
     * 不能被实例化
     */
    private function __construct()
    {
        
    }
    
    /**
     * 计算买入股票时数据变更
     * @author  jianwie
     * @param $stock    股票代码
     * @param $buy_price   int 用多少钱去购买这个股票
     * @param $buy_price    numeric 股票价格
     */
    public static function BuyStockCalculate($stock, $buy_price, $stock_price)
    {
        //基础校验
        if (!is_string($stock) || empty($stock) || !is_numeric($buy_price) || $buy_price <= 0 || !is_numeric($stock_price) || $stock_price <= 0) {
            throw new JsonException(10000);
        }
        
        //以总金额来算看需要交多少税以及佣金等
        $stock_charge_value = self::CalculateStockChargeValue($stock, $buy_price, config('site.stock.type.stock_in'));
        
        //总成本
        $charge_total_value = $stock_charge_value['commission_money'] + $stock_charge_value['stamp_duty_money'] + $stock_charge_value['transfer_fee_money'];
        
        //最终能使用的钱
        $final_use_money = $buy_price - $charge_total_value;
        
        //算出这些钱能买多少股
        $buy_stock_quantity = self::CalculateAmountStock($final_use_money, $stock_price);
        
        //重新计算现在的购买 $buy_stock_quantity 股所需的金额
        $final_buy_money = $buy_stock_quantity * $stock_price;
        $final_buy_money = Helper::sprint2f($final_buy_money);
        
        //重新计算需要交多少税以及佣金等
        $final_stock_charge_value =  self::CalculateStockChargeValue($stock, $final_buy_money, config('site.stock.type.stock_in'));
    
        //总成本
        $final_charge_total_value = $final_stock_charge_value['commission_money'] + $final_stock_charge_value['stamp_duty_money'] + $final_stock_charge_value['transfer_fee_money'];
        $final_charge_total_value = Helper::sprint2f($final_charge_total_value);
    
        $response_arr = array();
        $response_arr['stock_code'] = $stock;
        $response_arr['stock_price'] = $stock_price;
        $response_arr['buy_stock_quantity'] = $buy_stock_quantity;
        $response_arr['final_buy_money'] = $final_buy_money;
        $response_arr['charge_total_value'] = $final_charge_total_value;
        
        $response_arr = array_merge($response_arr,$final_stock_charge_value);
        
        return $response_arr;
    }
    
    
    /**
     * 通过购买的股票数量计算买入股票时数据变更
     * @author  jianwie
     * @param $stock    股票代码
     * @param $buy_price   int 用多少钱去购买这个股票
     * @param $buy_price    numeric 股票价格
     */
    public static function BuyStockCalculateByNumber($stock, $buy_number, $stock_price)
    {
        //基础校验
        if (!is_string($stock) || empty($stock) || !is_numeric($buy_number) || $buy_number <= 0 || !is_numeric($stock_price) || $stock_price <= 0) {
            throw new JsonException(10000);
        }
        
        
        //过滤一下购买数量
        $buy_stock_quantity = self::FixedStockBuyNumber($buy_number);
        
        //重新计算现在的购买 $buy_stock_quantity 股所需的金额
        $final_buy_money = $buy_stock_quantity * $stock_price;
        $final_buy_money = Helper::sprint2f($final_buy_money);
        
        //重新计算需要交多少税以及佣金等
        $final_stock_charge_value =  self::CalculateStockChargeValue($stock, $final_buy_money, config('site.stock.type.stock_in'));
    
        //总成本
        $final_charge_total_value = $final_stock_charge_value['commission_money'] + $final_stock_charge_value['stamp_duty_money'] + $final_stock_charge_value['transfer_fee_money'];
        $final_charge_total_value = Helper::sprint2f($final_charge_total_value);
        
        $response_arr = array();
        $response_arr['stock_code'] = $stock;
        $response_arr['stock_price'] = $stock_price;
        $response_arr['stock_total_price'] = $final_buy_money;
        $response_arr['buy_stock_quantity'] = $buy_stock_quantity;
        $response_arr['final_buy_money'] = Helper::sprint2f($final_buy_money + $final_charge_total_value);
        $response_arr['charge_total_value'] = $final_charge_total_value;
        
        $response_arr = array_merge($response_arr,$final_stock_charge_value);
        
        return $response_arr;
    }
    
    
    /**
     * 通过购买的股票数量计算买入股票时数据变更
     * @author  jianwie
     * @param $stock    股票代码
     * @param $buy_price   int 用多少钱去购买这个股票
     * @param $buy_price    numeric 股票价格
     * @param $type 类型  in:买入,out:卖出
     */
    public static function SellStockCalculateByNumber($stock, $buy_number, $stock_price)
    {
        //基础校验
        if (!is_string($stock) || empty($stock) || !is_numeric($buy_number) || $buy_number <= 0 || !is_numeric($stock_price) || $stock_price <= 0) {
            throw new JsonException(10000);
        }
        
        //过滤一下购买数量
        $buy_stock_quantity = self::FixedStockBuyNumber($buy_number);
        
        //重新计算现在的购买 $buy_stock_quantity 股所需的金额
        $final_buy_money = $buy_stock_quantity * $stock_price;
        $final_buy_money = Helper::sprint2f($final_buy_money);
        
        //重新计算需要交多少税以及佣金等
        $final_stock_charge_value =  self::CalculateStockChargeValue($stock, $final_buy_money, config('site.stock.type.stock_out'));
        
        //总成本
        $final_charge_total_value = $final_stock_charge_value['commission_money'] + $final_stock_charge_value['stamp_duty_money'] + $final_stock_charge_value['transfer_fee_money'];
        $final_charge_total_value = Helper::sprint2f($final_charge_total_value);
        
        
        $response_arr = array();
        $response_arr['stock_code'] = $stock;
        $response_arr['stock_price'] = $stock_price;
        $response_arr['stock_total_price'] = $final_buy_money;
        $response_arr['buy_stock_quantity'] = $buy_stock_quantity;
        $response_arr['charge_total_value'] = $final_charge_total_value;
        $response_arr['final_buy_money'] = Helper::sprint2f($final_buy_money - $final_charge_total_value);
        
        $response_arr = array_merge($response_arr,$final_stock_charge_value);
        
        return $response_arr;
    }
    
    /**
     * 计算资金能购买多少手
     * @author  jianwei
     * @param   $money  numeric 金额
     * @param   $stock_price    numeric 股票购买价
     */
    public static function CalculateAmountStock($money, $stock_price)
    {
        if (!is_numeric($money) || $money <= 0 || !is_numeric($stock_price) || $stock_price <= 0) {
            throw new JsonException(10000);
        }
        //从配置中获取一手至少对少股
        $board_lot = config('stock.board_lot');
        
        //先取得最多能买多少股
        $quantity = $money / $stock_price;
        
        $board_lot_quantity = (int)($quantity / $board_lot) * $board_lot;
        
        if ($board_lot_quantity < $board_lot) {
            throw new JsonException(20000);
        }
        
        return $board_lot_quantity;
    }
    
    /**
     * 计算税收以及佣金之类的
     * @author  jianwei
     * @param $stock    string  股票代码
     * @param   $buy_price  购买的价格
     * @param $type enum    枚举，in:买入,out:卖出
     */
    public static function CalculateStockChargeValue($stock, $buy_price, $type)
    {
        $allow_type = array(config('site.stock.type.stock_in'), config('site.stock.type.stock_out'));
        
        if (!is_numeric($buy_price) || $buy_price <= 0 || !in_array($type, $allow_type)) {
            throw new JsonException(10000);
        }
        
        //获取股票信息
        //$stock_plate_config = Stock::CheckStockSource($stock);
        
        //计算佣金
        $commission_money = self::CalculateStockCommission($buy_price);
    
        $transfer_fee_money = self::CalculateStockTransferFee($stock, $buy_price, $type);
        
        //印花税，印花税收卖出时收取的
        $stamp_duty_money = 0;
        if (config('site.stock.type.stock_out') == $type) {
            $stamp_duty_money = self::CalculateStockStampDuty($buy_price);
        }
        $stamp_duty_money = Helper::sprint2f($stamp_duty_money);
        
        $response = array();
        //佣金
        $response['commission_money'] = $commission_money;
        //印花税
        $response['stamp_duty_money'] = $stamp_duty_money;
        //过户费
        $response['transfer_fee_money'] = $transfer_fee_money;
        
        return $response;
    }
    
    /**
     * 计算过户费
     * @author  jianwei
     * @param $stock_code   string  股票代码
     * @param $buy_price    numeric     购买数量
     * @param $type enum    类型，in:买入,out:卖出
     */
    public static function CalculateStockTransferFee($stock_code, $buy_price, $type)
    {
        $allow_type = [config('site.stock.type.stock_in'),config('site.stock.type.stock_out')];
        
        if (!is_numeric($buy_price) || $buy_price <= 0 || !in_array($type, $allow_type)) {
            throw new JsonException(10000);
        }
    
        //获取股票信息
        $stock_plate_config = Stock::CheckStockSource($stock_code);
    
        $transfer_fee_money = 0;
        if($stock_plate_config['code'] == config('stock.plate.shzb_key.code')){
            if(config('site.stock.type.stock_in') == $type) {
                //计算过户费
                $transfer_fee_money = $buy_price * $stock_plate_config['transfer_fee'];
            }else if(config('site.stock.type.stock_out') == $type){
                $transfer_fee_money = $buy_price * $stock_plate_config['transfer_fee'];
            }
        }
        
        if($transfer_fee_money < $stock_plate_config['min_transfer_fee']){
            $transfer_fee_money = $stock_plate_config['min_transfer_fee'];
        }
    
        $transfer_fee_money = Helper::sprint2f($transfer_fee_money);
        
        return $transfer_fee_money;
    }
    
    /**
     * 计算佣金
     * @author  jianwei
     * @param $buy_price    numeric
     */
    public static function CalculateStockCommission($buy_price)
    {
        if (!is_numeric($buy_price) || $buy_price <= 0) {
            throw new JsonException(10000);
        }
        
        $commission = config('stock.commission');
        $min_commission = config('stock.min_commission');
        
        $commission_money = $commission * $buy_price;
        
        $commission_money = $min_commission > $commission_money ? $min_commission : $commission_money;
        
        return Helper::sprint2f(round($commission_money, 2));
    }
    
    /**
     * 计算印花税
     * @author  jianwei
     * @param $buy_price    numeric
     */
    public static function CalculateStockStampDuty($buy_price)
    {
        if (!is_numeric($buy_price) || $buy_price <= 0) {
            throw new JsonException(10000);
        }
        
        $stamp_duty = config('stock.stamp_duty');
        
        $stamp_duty_money = $stamp_duty * $buy_price;
        
        return Helper::sprint2f(round($stamp_duty_money, 2));
    }
    
    /**
     * 检查购买股票的数量是否有效
     * @author  jianwei
     */
    public static function CheckStockBuyNumber($buy_number)
    {
        if (!is_numeric($buy_number) || $buy_number <= 0) {
            throw new JsonException(10000);
        }
        
        //从配置中获取一手至少对少股
        $board_lot = config('stock.board_lot');
        
        $final_buy_number = $buy_number / $board_lot;
        
        if ($final_buy_number != (int)$final_buy_number) {
            throw new JsonException(20000);
        }
        
        return $final_buy_number;
    }
    
    /**
     * 检查购买股票的数量是否有效
     * @author  jianwei
     */
    public static function FixedStockBuyNumber($buy_number)
    {
        if (!is_numeric($buy_number) || $buy_number <= 0) {
            throw new JsonException(10000);
        }
        
        //从配置中获取一手至少对少股
        $board_lot = config('stock.board_lot');
        
        $final_buy_number = (int)($buy_number / $board_lot) * $board_lot;
        
        return $final_buy_number;
    }
    
    /**
     * 判断一个股票是来自哪个板块的
     * @author  jianwei
     * @param $stock    string 股票名称
     * 上海就一个主板市场，股票代码前三位为：600、601、603
     * 深圳有主板，中小板，创业板之分
     * 主板代码前三位：000、001
     * 中小板前三位：002
     * 创业板前三位：300
     */
    public static function CheckStockSource($stock)
    {
        if (!is_string($stock)) {
            throw new JsonException(10000);
        }
        
        //正则：用于判断
        $stock_reg_list = (array)config('stock.regexp_group');
        
        $plate = '';
        foreach ($stock_reg_list as $lk => $lv) {
            if (count($lv) != 2) {
                continue;
            }
            
            if (preg_match($lv['regexp'], $stock)) {
                $plate = $lv['plate'];
                break;
            }
        }
        
        $plate_config = config('stock.plate.' . $plate);
        
        if (empty($plate_config)) {
            throw new JsonException(20001);
        }
        
        return $plate_config;
    }
    
    /**
     * 获取当前股票价格
     * @author  jianwei
     */
    public static function GetStockPrice($stock_code)
    {
        //获取股票信息
        $stock_plate_config = Stock::CheckStockSource($stock_code);
    
        //获取该股票当前信息
        $stock_tmp_key = $stock_plate_config['prefix'].$stock_code;
    
        $stock_info = StockApi::getStockInfoFromGtimg($stock_tmp_key,['day']);
    
        if(isset($stock_info['code']) && $stock_info['code'] != 0){
            throw new JsonException(20002);
        }
    
        $stock_price = 0;
        $stock_data = $stock_info['data'][$stock_tmp_key];
    
        if(isset($stock_data['day'])){
            //获取最后一个元素，一般为当前的
            $current_stock_data = end($stock_data['day']);
            //第三个元素为当前价格
            if(isset($current_stock_data[2])){
                $stock_price = $current_stock_data[2];
            }
        }
    
        return $stock_price;
    }
    
}
