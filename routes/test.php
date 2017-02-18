<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('/stock/buy-price','IndexController@buyPrice');
Route::any('/stock/buy-number','IndexController@buyNumber');
Route::any('/stock/sell-number','IndexController@sellNumber');

//计算
Route::any('/stock/calculate-cost','IndexController@calculateCost');

//股票买卖操作
Route::any('/stock/show-handle','IndexController@showHanlePage');


Route::resource('/stock','IndexController');
