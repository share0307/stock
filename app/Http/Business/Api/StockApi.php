<?php
    
namespace App\Http\Business\Api;

use App\Exceptions\JsonException;
use App\Http\Common\Helper;

/**
 * Class StockApi
 * @package App\Http\Api
 * @author  jianwei
 * @created at  2016-7-31
 * Api 基类
 */
class StockApi extends ApiBase {
    
    /**
     * 获取当前股票信息
     * @author  jianwei
     * @param   $stock  string  股票代码
     * @param $param    array 对外接口中的params参数
     * @param $condition    array   其他参数
     */
    public static function getStockInfoFromGtimg($stock,array $param = [], array $condition = [])
    {
        if(!is_string($stock) || empty($stock)){
            throw new JsonException(10000);
        }
    
        array_unshift($param,$stock);
        
        $condition['param'] = implode(',', $param);
        
        //$api = config('stockapi.gtimg.check_stock_info');
        
        //$full_api = $api.'?'.http_build_query($condition);
    
        $full_api = 'http://web.ifzq.gtimg.cn/appstock/app/fqkline/get?param='.$stock.',day,,,2,fq';
        
        return self::jsRequest($full_api);
    }
    
}
