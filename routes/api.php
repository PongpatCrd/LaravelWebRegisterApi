<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [LoginController::class, 'loginUser']);

Route::prefix('register')->group(function () {
    Route::get('get_register_chart_data', [RegisterController::class, 'getRegisterCountByDate']);
    Route::get('get_activated_chart_data', [RegisterController::class, 'getActivatedCountByDate']);

    Route::post('create_user', [RegisterController::class, 'createUser']);
});
