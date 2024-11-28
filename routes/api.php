<?php

use App\Http\Controllers\Order\OrderController;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;




Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me'])->middleware('isAuth');
});

Route::group(['middleware' => 'isAuth'],function (){
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/changeStatus/{id}', [OrderController::class, 'changeStatus']);
    Route::get('/orders/export-csv', [OrderController::class, 'exportToCsv'])->name('orders.export.csv');
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}', [OrderController::class, 'update']);

    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);


});


