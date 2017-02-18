<?php

namespace App\Http\Common;
use App\Exceptions\JsonException;
use Predis\Client;

class Helper
{
    /**
     * 密码加密
     * @param $password 需要加密的密码
     * @param $random 随机数字，空时会自动生成对应的随机数字
     * @return array ['encrypt' => xxx, 'random' => xxx]
     * @created 2016-07-27
     * @auth chentengfeng
     */
    public static function passwordEncrypt($password, $random='')
    {
        if (empty($random)) {
            $random = self::randomStr(10);
            return ['encrypt' => substr(md5($password . $random), 0, -2), 'random' => $random];
        }
        
        return substr(md5($password . $random), 0, -2);
    }
    
    
    /**
     * 生成一串随机字母
     * @param $length //输出的字符串长度
     * @return string
     * @created 2016-07-27
     * @auth chentengfeng
     * @update -> 添加数字随机数 -> weixinhua 2016-07-29
     */
    public static function randomStr($length,$numeric = false)
    {
        $string = $numeric == false ? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ':'123456789';
        
        $str = '';
        mt_srand(10000000*(double)microtime());
        
        for($i = 0,$str_len = strlen($string)-1 ; $i < $length; $i++) {
            $str .= $string[mt_rand(0, $str_len)];
        }
        
        return $str;
    }
    
    
    
    
    /**
     * 获取随机密码
     *
     * @return string
     */
    public static function queryRandomPassword($length)
    {
        $length = is_numeric($length) && $length > 0 ? $length : 6;
        $chars    = '01234567890123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组$chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }
    
    /*
     * curl_get提交方式
     * @param string $url 请求链接
     * @oaram int $req_number 失败请求次数
     * @param int $timeout 请求时间
     *
     */
    public static function curlGet($url, $req_number = 2, $timeout=30) {
        
        //防止因网络原因而高层无法获取
        $cnt = 0;
        $result = FALSE;
        while ( $cnt < $req_number && $result === FALSE) {
            $cnt++;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            //禁止直接显示获取的内容 重要
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //在发起连接前等待的时间，如果设置为0，则无限等待。
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            //不验证证书下同
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //SSL验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch); //获取
            curl_close($ch);
        }//end func curl_get
        
        //获取数据
        $data = $result ? $result : null;
        
        return $data;
    }//end func curlGet
    
    
    /**
     * curl_get提交方式
     * @param string $url 请求链接
     * @param array $post_data 请求数据
     * @param string $post_type 请求类型(json)
     *
     */
    public static function curlPost($url, $post_data = '', $post_type = '', $curl_params = [])
    {
        //初始化curl
        $ch = curl_init();
        //设置请求地址
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置curl参数，要求结果是否输出到屏幕上，为true的时候是不返回到网页中
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        //https ssl 验证
        if (!empty($curl_params['ssl'])) {
            $ssl = $curl_params['ssl'];
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //验证站点名
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // 只信任CA颁布的证书
            if (!empty($ssl['sslca'])) {
                curl_setopt($ch, CURLOPT_CAINFO, $ssl['sslca']);
            }
            if (!empty($ssl['sslcert'])) {
                curl_setopt($ch, CURLOPT_SSLCERT, $ssl['sslcert']);
            }
            if ($ssl['sslkey']) {
                curl_setopt($ch, CURLOPT_SSLKEY, $ssl['sslkey']);
            }
        } else {
            //验证站点名
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //是否验证https(当请求链接为https时自动验证，强制为false)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 只信任CA颁布的证书
        }
        
        //设置post提交方式
        curl_setopt($ch, CURLOPT_POST, 1);
        //设置post字段
        $post_data = is_array($post_data) ? http_build_query($post_data) : $post_data;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        
        //判断是否json提交
        if ('json' == $post_type) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Expect:',
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($post_data))
            );
        }
        
        //运行curl
        $output = curl_exec($ch);
        //关闭curl
        curl_close($ch);
        //返回结果
        return $output;
    }//end func curlPost
    
    
    /**
     * @author  jianwei
     * 加密,生成加密后的字符串以及解密密钥,
     * @param   $token string access_token
     * @param   $key   string 约定的key值
     * @param   $type  int 0为加密，1为解密
     * @notic   其实并不会太安全,只是作为简单的加密处理
     */
    public static function encrypt($token,$key,$type = 0){
        if(empty($token)){
            return false;
        }
        //$key = sha1($key);
        if(!$type){
            //加密
            if(empty($key) || mb_strlen($token) > mb_strlen($key)){
                //足够长的随机种子
                $key = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_';
            }
            
            $encrypt_str = base64_encode($token);
            
            //生成随机key
            $key = substr(str_shuffle($key),0,strlen($encrypt_str));
            
            $sign_token = $encrypt_str ^ $key;
            
            return ['token'=>$sign_token,'key'=>$key];
        }
        return base64_decode($token ^ $key);
    }
    
    /**
     * 字典加密
     * @param $hashtable    需要加密的数组
     * @param $secret       安全密钥
     * @param bool $qhs
     * @return string
     */
    public static function sign($hashtable, $secret)
    {
        //"g56ef@4f%df$%hyU*"
        // 第一步：把字典按Key的字母顺序排序
        ksort($hashtable);
        $str = $secret;
        // 第二步：把所有参数名和参数值串在一起
        foreach($hashtable as $key => $value){
            $str .= $key.$value;
        }
        
        // 第三步：使用MD5加密
        $sign = md5($str);
        return strtoupper($sign);
    }
    
    
    /**
     * 过滤字符串 && 数字 && 数组 && 对象的空格
     * @author  jianwei
     * @param   需要过滤的数据
     * @param   $charlist = " \t\n\r\0\x0B",过滤的模式
     * @notic   支持多维
     */
    public static function trimAny(&$data, $charlist = " \t\n\r\0\x0B")
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $data[$key] = self::trimAny($value, $charlist);
                } else {
                    $data[$key] = self::trimAny($value, $charlist);
                }
            }
        } else if (is_string($data)) {
            $data = trim($data, $charlist);
        }
        return $data;
    }
    
    
    /*
     * 重新封装json解码函数,可防止因为编码而造成得解码失败
     * @param   $json   string      json字符串
     * @param   $coding array     把什么编码转成UTF-8
     * @notice:暂时此json格式化工具函数只支持utf-8的解码
     * @author  jianwei
     */
    public static function js_decode($json,array $coding = [])
    {
        //去除空格
        $json = static::trimAny($json);
        if(!is_string($json) || empty($json)){
            //return $json;
            return array();
        }
        //检查当前编码是否utf-8等
        $encoding = 'UTF-8';
        if(!mb_check_encoding($json,$encoding)){
            //不管如何,都默认存在以下几种编码的转码
            $coding = array_merge(['ASCII,UTF-8','ISO-8859-1'],$coding);
            $coding_str = implode(',',$coding);
            $json = mb_convert_encoding($json, $encoding, $coding_str);
            //移除BOM头,否则json_decode失败
            if (substr($json, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) {
                $json = substr($json, 3);
            }
        }
        $decode_data = (array)json_decode($json,1);
        if(empty($decode_data)){
            return [];
        }
        return $decode_data;
    }
    
    
    /**
     * 获取当前时间
     * @author  jianwei
     * @param   $flag   double  当$flag 为 true 时,等同于 time()
     */
    public static function getNow($flag = false)
    {
        static $now_time = null;
        if(null === $now_time){
            $now_time = date('YmdHis',time());
        }
        
        if(true === $flag){
            return date('YmdHis',time());
        }
        
        return $now_time;
    }
    
    /**
     * 格式化时间
     * @author  jianwei
     * @param   $time   int     需要格式化的时间
     * @param   $format_temp    string      格式化的格式
     */
    public static function formatTime($time,$format_temp = 'Y-m-d H:i:s')
    {
        $timestamp = (int)strtotime($time);
        
        return date($format_temp,$timestamp);
    }
    
    /**
     * 获取格式化时间
     * @author  jianwei
     */
    public static function getFormatTime($format = 'Y-m-d H:i:s',$flag = false)
    {
        static $now_time = null;
        if(null === $now_time){
            $now_time = date($format,time());
        }
    
        if(true === $flag){
            return date($format,time());
        }
    
        return $now_time;
    }
    
    /**
     * 检查手机号码是否合法
     * @author  jianwei
     */
    public static function checkMobile($mobile)
    {
        $partten = '/^1[34578]{1}\d{9}$/';
        return preg_match($partten,$mobile,$matches);
    }
    
    /**
     * 检查接口请求是否成功
     * @author  jianwei
     */
    public static function checkApiSuccess($api_result)
    {
        if(!isset($api_result['code']) || !isset($api_result['data'])){
            throw new JsonException(10003);
        }
        if(is_array($api_result) && isset($api_result['code']) && $api_result['code'] != 0){
            return false;
        }
        
        return $api_result['data'];
    }
    
    /**
     * 获取数组中的数字
     * @author  jianwei
     */
    public static function ArrayFilterNum(array $arr = [])
    {
        $arr = Helper::trimAny($arr);
        $filter_num_func = function($val){
            return is_numeric($val);
        };
        
        return array_filter($arr,$filter_num_func);
    }
    
    
    /**
     * 检查某个数组中是否有重复数据
     * @author  jianwei
     * @param   $arr    array   数组
     */
    public static function checkArrRepeat(array $arr)
    {
        return max(array_count_values($arr)) > 1 ? true : false;
    }
    
    
    /**
     * 创建图片资源链接
     * @author  jianwei
     * @return  string  图片链接，当出现错误时，返回''
     */
    public static function buildImageUrl($image_id,$condition = [],$api_key = 'default')
    {
        $api_url = config('imageapi.image_api.'.$api_key);
    
        $api_url = Helper::trimAny($api_url);
    
        $imag_url = '';
        if(empty($api_url)){
            return $imag_url;
        }
        
        $api_param = http_build_query($condition);
        $imag_url = $api_url . $image_id;
        if(!empty($api_param)) {
            $imag_url.= '?' . $api_param;
        }
    
        return $imag_url;
    }
    
    /**
     * 获取图片上传链接
     * @author  jianwei
     */
    public static function getImageUploadUrl()
    {
        return config('imageapi.upload');
    }
    
    /**
     * 构建链接
     * @author  jianwei
     */
    public static function buildUrl($config_url_name,array $condition = [])
    {
        $url = config('site_url.url.'.$config_url_name);
        
        if(!empty($condition)){
            $url.= '?'.http_build_query($condition);
        }
        
        return $url;
    }
    
    /**
     * 获取redis链接
     *@author   jianwei
     * @param $param    参数..
     */
    public static function Predis(array $param = [])
    {
        return app('redis');
        static $redis = null;
        
        if($redis === null){
            $redis = new Client($param);
        }
        
        return $redis;
    }
    
    /**
     * 保留2位小数点
     * @author  jianwei
     */
    public static function sprint2f($num)
    {
        return sprintf('%01.2f',$num);
    }
    
}
