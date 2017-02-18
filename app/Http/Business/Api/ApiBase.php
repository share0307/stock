<?php
    
namespace App\Http\Business\Api;
use App\Exceptions\JsonException;
use App\Http\Common\Helper;

/**
 * Class ApiBase
 * @package App\Http\Api
 * @author  jianwei
 * @created at  2016-7-31
 * Api 基类
 */
class ApiBase {
    
    
    /**
     * buildParamStr,用于请求直播云api
     * @auhtor  jianwei
     * 拼接参数
     * @param  array $request_params  请求参数
     * @param  string $request_method 请求方法
     * @return
     */
    protected static function buildParamStr($request_params, $request_method = 'GET')
    {
        $paramStr = '';
        ksort($request_params);
        $i = 0;
        foreach ($request_params as $key => $value) {
            if ($key == 'Signature') {
                continue;
            }
            // 排除上传文件的参数
            if ($request_method == 'POST' && substr($value, 0, 1) == '@') {
                continue;
            }
            // 把 参数中的 _ 替换成 .
            if (strpos($key, '_')) {
                $key = str_replace('_', '.', $key);
            }
            
            if ($i == 0) {
                $paramStr .= '?';
            } else {
                $paramStr .= '&';
            }
            
            $paramStr .= $key . '=' . $value;
            
            ++$i;
        }
        
        return $paramStr;
    }
    
    
    /**
     * makeSignPlainText,用于请求直播云api
     * 生成拼接签名源文字符串
     * @param  array $request_params  请求参数
     * @param  string $request_method 请求方法
     * @param  string $request_host   接口域名
     * @param  string $request_path   url路径
     * @return
     */
    protected static function makeSignPlainText($request_params,$request_method = 'GET', $request_host,$request_path = '/v2/index.php')
    {
        
        $url = $request_host . $request_path;
        
        // 取出所有的参数
        $paramStr = self::buildParamStr($request_params, $request_method);
        
        $plainText = $request_method . $url . $paramStr;
        
        return $plainText;
    }
    
    /**
     * sign,用于请求直播云
     * 生成签名
     * @param  string $srcStr    拼接签名源文字符串
     * @param  string $secretKey secretKey
     * @param  string $method    请求方法
     * @return  string
     */
    protected static function sign($srcStr, $method = 'HmacSHA1')
    {
        $secretKey = config('qcloudapi.secret_key');
        
        switch ($method) {
            case 'HmacSHA1':
                $retStr = base64_encode(hash_hmac('sha1', $srcStr, $secretKey, true));
                break;
            // case 'HmacSHA256':
            // $retStr = base64_encode(hash_hmac('sha256', $srcStr, $secretKey, true));
            // break;
            default:
                //throw new Exception($method . ' is not a supported encrypt method');
                return false;
                break;
        }
        
        return $retStr;
    }
    
    
    /**
     * 组装qcloud 请求
     * @author  jianwei
     */
    protected static function buildQueryParam($param,$request_method,$request_host,$request_path = '')
    {
        //校验..
        //coding..
        
        //地区
        $api_region_list = config('qcloudapi.region_list');
        $api_region_list_key = array_keys($api_region_list);
    
        if(isset($common_param_arr['Region']) && in_array($common_param_arr['Region'],$api_region_list_key)){
            $param['Region'] = $common_param_arr['Region'];
        }
        
        //当前时间戳
        $param['Timestamp'] = time();
        
        //随机正整数，与 Timestamp 联合起来, 用于防止重放攻击
        $param['Nonce'] = mt_rand(1,65535);
        
        //由腾讯云平台上申请的标识身份的 SecretId 和 SecretKey, 其中 SecretKey 会用来生成 Signature
        $param['SecretId'] = config('qcloudapi.secret_id');
    
        $request_path = !empty($request_path) ? $request_path : '/v2/index.php';
        
        //拼接字符串
        $plainText = self::makeSignPlainText($param,$request_method,$request_host,$request_path);
        
        //请求签名，用来验证此次请求的合法性,
        $param['Signature'] = self::sign($plainText);
        
        return $param;
    }
    
    /**
     * 组装直播云真实请求地址
     * @author  jianwei
     * @param   $url    请求地址
     * @return  String
     */
    protected static function buildRequestUrl($url,$request_path = '/v2/index.php')
    {
        $url = (string)$url;
        
        $format_url = 'https://%s'.$request_path;
        
        $full_url = sprintf($format_url,$url);
        
        return $full_url;
    }//end func buildRequestUrl
    
    
    /**
     * 请求封装
     * @author  jianwei
     * @created 2016-7-31
     * @param   $api_links  string  请求地址
     * @param   $request_method     请求方式，get && post
     * @param   $api_param  array   请求的参数数组
     */
    protected static function buildQcloudQuery($api_links,$request_method = 'POST',array $api_param = [])
    {
        //默认为post
        $request_method = is_string($request_method) && in_array($request_method,['GET','POST']) ? $request_method : 'POST';
        
        $request_api_url = self::buildRequestUrl($api_links);
    
        $api_real_param = self::buildQueryParam($api_param,$request_method,$api_links,'');
    
        if( 'POST' == $request_method){
            $api_result = Helper::curlPost($request_api_url,$api_real_param);
        }else if( 'GET' == $request_method ){
            $api_real_param_str = is_array($api_real_param) ? http_build_query($api_real_param) : $api_real_param;
            $full_request_api_url = $request_api_url.'?'.$api_real_param_str;
            
            $api_result = Helper::curlGet($full_request_api_url);
        }
        
        if( false === $api_result){
            //请求出错时，直接抛出错误
            throw new JsonException(20000);
        }
    
        $api_info = Helper::js_decode($api_result);
        
        if(!isset($api_info['code']) || ( 0 != $api_info['code'])){
            throw new JsonException(20001,$api_info);
        }
        
        return $api_info;
    }
    
    
    /**
     * 用于接口返回格式是json的请求
     * @author  jianwei
     */
    protected static function jsRequest($api,array $param = [],$method = 'post')
    {
        if(!in_array($method,['post','get'])){
            throw new JsonException(10000);
        }
        
        if( 'get' == $method ){
            $full_api = $api.'?'.http_build_query($param);
            $result = Helper::curlGet($full_api);
        }else{
            $result = Helper::curlPost($api,$param);
        }
        
        $data = Helper::js_decode($result);
        
        return $data;
    }
    
}
