<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login',[UserController::class, 'authenticate']);
Route::get('validar',[UserController::class, 'validation']);

Route::get('Authenticate',[UserController::class, 'getAuthenticatedUser']);
Route::post('user',[UserController::class, 'register']);
Route::get('user/list',[UserController::class, 'list']);
Route::put('user/update/{uuid}',[UserController::class, 'update']);
Route::get('user/edit/{uuid}',[UserController::class, 'edit']);
Route::delete('user/delete/{uuid}',[UserController::class, 'delete']);

