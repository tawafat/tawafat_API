<?php

use App\Http\Controllers\AttachController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('auth/user', function (Request $request) {
    return $request->user();
});
Route::post('/registration', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



Route::get('', function () {
    return ['status' => 'working v1.2.1'];
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('job/me', [JobController::class, 'assignedToMe']);
    Route::get('job/type/{type}', [JobController::class, 'index']);
    Route::get('job/{id}/jobLogs', [JobController::class, 'jobLogs']);
    Route::post('job/{id}/assignTo', [JobController::class, 'assignTo']);
    Route::post('job/{id}/startEnd', [JobController::class, 'startEnd']);
    Route::post('job/{id}/storeDetails', [JobController::class, 'storeDetails']);
    Route::resource('job', JobController::class);


    Route::resource('category', CategoryController::class);
    Route::resource('complain', ComplainController::class);
    Route::resource('user', UserController::class);


    Route::post('/attach', [AttachController::class, 'upload']);

    Route::get('/attach/{id}/info', [AttachController::class, 'show']);
    Route::get('/attaches', [AttachController::class, 'index']);


    Route::get('/dashboard', [DashboardController::class, 'index']);
});
Route::get('/attach/{id}', [AttachController::class, 'preview']);

/*Route::get('/jobs', [JobController::class, 'index']);
Route::post('/job', [JobController::class, 'store']);
Route::get('/job/{id}', [JobController::class, 'show']);*/
