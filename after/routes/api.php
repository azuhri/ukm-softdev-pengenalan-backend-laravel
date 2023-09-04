<?php

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(APIController::class)->group(function() {
    Route::prefix("v1")->group(function() {
        Route::prefix("user")->group(function() {
            Route::post("/","createUser");
            Route::put("/","updateUser");
        });
        Route::prefix("bank-account")->group(function() {
            Route::get("{norek}", "getDataBank");
            Route::post("/", "topUpSaldo");
            Route::post("/transfer", "transferAmount");
            Route::get("/{norek}/mutations", "getMutation");
        });
    });
});
