<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    
    
    public function jsonFormat($data)
    {
        if(is_object($data)){
            if(method_exists($data,'toArray')){
                $data = $data->toArray();
            }
        }
        
        if(isset($data['code'])){
            return $data;
        }
        
        if(!is_array($data)){
            $data = (array)$data;
        }
        
        return [
            'code' => 0,
            'msg'  => '成功',
            'data' => $data,
        ];
    }
}
