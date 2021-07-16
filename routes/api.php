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

Route::group(['namespace' => 'Api'], function() {
    Route::any('index', 'IndexController@Index');
    Route::any('addtask', 'IndexController@AddTask');
    Route::any('decompose', 'IndexController@Decompose');
    Route::any('typelist', 'IndexController@TypeList');
    Route::any('userlist', 'IndexController@Userlist');
    Route::any('tasklist', 'IndexController@TaskList');
    Route::any('leader', 'IndexController@Leader');
    Route::any('member', 'IndexController@Member');
    Route::any('personal', 'IndexController@Personal');
    Route::any('taskdetail', 'IndexController@Taskdetail');
    Route::any('taskdelete', 'IndexController@Taskdelete');
    Route::any('weekly', 'IndexController@Weekly');
});
