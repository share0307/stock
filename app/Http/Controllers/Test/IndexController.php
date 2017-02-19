<?php

namespace App\Http\Controllers\Test;

use App\Http\Business\Api\StockApi;
use App\Http\Business\SimulatedStockBusiness;
use App\Http\Common\Stock;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    
    /**
     * @param Request $request
     * @return array
     * @notice:测试用
     */
    public function index(Request $request)
    {
        return view('test.stock');
    }
    
    /**
     * 通过数量去计算成本
     * @author  jianwei
     * @notice:测试用
     */
    public function buyPrice(Request $request)
    {
        //股票代码
        $stock_code = $request->stock;
    
        //数量
        $buy_price = $request->buy_price;
    
        //股票价格
        $stock_price = Stock::GetStockPrice($stock_code);
    
        $response = Stock::BuyStockCalculate($stock_code, $buy_price, $stock_price);
    
        return $this->jsonFormat($response);
    }
    
    /**
     * 通过购买股票的数量去获取
     * @author  jianwei
     * @notice:测试用
     */
    public function buyNumber(Request $request)
    {
        //股票代码
        $stock_code = $request->stock;
    
        //数量
        $buy_number = $request->buy_number;
    
        //股票价格
        $stock_price = Stock::GetStockPrice($stock_code);
    
        $response = Stock::BuyStockCalculateByNumber($stock_code, $buy_number, $stock_price);
    
        return $this->jsonFormat($response);
    }
    
    
    /**
     * 通过购买股票的数量去获取
     * @author  jianwei
     * @notice:测试用
     */
    public function sellNumber(Request $request)
    {
        //股票代码
        $stock_code = $request->stock;
        
        //数量
        $buy_number = $request->buy_number;
    
        //股票价格
        $stock_price = Stock::GetStockPrice($stock_code);
        
        $response = Stock::SellStockCalculateByNumber($stock_code, $buy_number, $stock_price);
        
        return $this->jsonFormat($response);
    }
    
    
    /**
     * 做整理,用于做股票交易的整合
     * @author  jianwei
     */
    public function calculateCost(Request $request)
    {
        //股票代码
        $stock_code = $request->stock;
    
        //数量
        $buy_number = $request->buy_number;
        
        //交易类型，out or in
        $type = $request->type;
    
        $stock_price = $request->stock_price;
        
        if(!is_numeric($stock_price) || $stock_price < 0) {
            //股票价格
            $stock_price = Stock::GetStockPrice($stock_code);
        }
    
        $response = array();
        if(config('site.stock.type.stock_out') == $type) {
            $response = Stock::SellStockCalculateByNumber($stock_code, $buy_number, $stock_price);
        }else{
            $response = Stock::BuyStockCalculateByNumber($stock_code, $buy_number, $stock_price);
        }
    
        return $this->jsonFormat($response);
    }
    
    /**
     * 显示页面
     * @author  jianwei
     */
    public function showHanlePage(){
        return '';
    }
    
    /**
     * 股票买入
     * @author  jianwei
     */
    public function handleStock(Request $request, SimulatedStockBusiness $simulated_stock_business)
    {
        //用户id
        $user_id = 999;
        
        //类型,in=>买入,out=>卖出
        $type = $request->type;
        
        //股票代码
        $stock_code = $request->stock_code;
        
        //股票数量
        $quantity = $request->quantity;
        
        //股票价格
        $stock_price = $request->stock_price;
        
        
        //操作股票
        $handle_response = $simulated_stock_business->userHandleStock($user_id,$stock_code,$quantity,$stock_price,$type);
        
        return $this->jsonFormat($handle_response);
    }
    
}
