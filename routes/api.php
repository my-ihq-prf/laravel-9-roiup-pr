<?php

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

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

// Route::post('authenticate', [AuthController::class, 'authenticate']);
// Route::post('authenticate', 'App\Http\Controllers\AuthController@authenticate');

Route::controller(App\Http\Controllers\AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout');
    Route::post('authenticate', 'authenticate');
    Route::post('all-users', 'allUsers');
});
Route::controller(App\Http\Controllers\LuckyLinkController::class)->group(function () {
    Route::post('gen-link', 'generate');
    Route::post('del-link', 'deactivate');
    Route::post('copy-link', 'copy');
    Route::post('check-link/{llnk}', 'check')->where('llnk', '(.*)');
});

Route::controller(App\Http\Controllers\LuckyNumberController::class)->group(function () {
    Route::post('gen-num', 'generate');
    Route::post('his-num', 'history');

});

