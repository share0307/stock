<?php

namespace App\Providers;

use App\Model\UserStockAccount;
use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    
    //延时加载
    protected $defer = true;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //用户股票账户
        $this->app->bind('UserStockAccountModel',UserStockAccount::class);
    }
    
    
    /**
     * 需要延时加载的模型
     * @author  jianwei
     */
    public function provides()
    {
        $provides_arr = array();
        $provides_arr[] = 'UserStockAccountModel';
        
        return $provides_arr;
    }
    
}
