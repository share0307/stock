<?php

namespace App\Providers;

use App\Model\UserStockAccount;
use App\Model\UserStockList;
use App\Model\UserStockLog;
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
        //用户所拥有的股票课表
        $this->app->bind('UserStockListModel',UserStockList::class);
        //用户操作股票记录
        $this->app->bind('UserStockLogModel',UserStockLog::class);
        
    }
    
    
    /**
     * 需要延时加载的模型
     * @author  jianwei
     */
    public function provides()
    {
        $provides_arr = array();
        $provides_arr[] = 'UserStockAccountModel';
        $provides_arr[] = 'UserStockListModel';
        return $provides_arr;
    }
    
}
