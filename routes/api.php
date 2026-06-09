<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\FoundRequestController;
use App\Http\Controllers\Api\AdoptionRequestController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\VolunteerController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\RewardOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware('throttle:10,1')->post('/login', [AuthController::class, 'login']);
    Route::middleware('throttle:3,1')->post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::middleware('throttle:5,1')->post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
| Эти маршруты нужны главной странице и публичным страницам.
| Их можно открывать без авторизации.
*/

Route::get('/animals', [AnimalController::class, 'index']);
Route::get('/animals/{animal}', [AnimalController::class, 'show']);

Route::get('/tasks', [TaskController::class, 'index']);
Route::get('/tasks/{task}', [TaskController::class, 'show']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

Route::get('/donations', [DonationController::class, 'index']);

Route::get('/volunteers', [VolunteerController::class, 'index']);


Route::get('/top-volunteers', [AdminUserController::class, 'topVolunteers']);

/*
|--------------------------------------------------------------------------
| Public form routes
|--------------------------------------------------------------------------
*/

Route::post('/found-requests', [FoundRequestController::class, 'store']);
Route::post('/adoption-requests', [AdoptionRequestController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Protected routes
|--------------------------------------------------------------------------
| Создание, редактирование и админские данные — только после входа.
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/animals', [AnimalController::class, 'store']);
    Route::put('/animals/{animal}', [AnimalController::class, 'update']);
    Route::delete('/animals/{animal}', [AnimalController::class, 'destroy']);

    Route::get('/found-requests', [FoundRequestController::class, 'index']);
    Route::put('/found-requests/{foundRequest}', [FoundRequestController::class, 'update']);
    Route::delete('/found-requests/{foundRequest}', [FoundRequestController::class, 'destroy']);

    Route::get('/adoption-requests', [AdoptionRequestController::class, 'index']);
    Route::put('/adoption-requests/{adoptionRequest}', [AdoptionRequestController::class, 'update']);
    Route::delete('/adoption-requests/{adoptionRequest}', [AdoptionRequestController::class, 'destroy']);

    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword']);
    Route::post('/users/{user}/points', [AdminUserController::class, 'addPoints']);
    Route::post('/users/{user}/avatar', [AdminUserController::class, 'updateAvatar']);

    Route::get('/best-volunteer', [AdminUserController::class, 'bestVolunteer']);

    Route::post('/news', [NewsController::class, 'store']);
    Route::put('/news/{news}', [NewsController::class, 'update']);
    Route::delete('/news/{news}', [NewsController::class, 'destroy']);

    Route::post('/donations', [DonationController::class, 'store']);
    Route::put('/donations/{donation}', [DonationController::class, 'update']);
    Route::delete('/donations/{donation}', [DonationController::class, 'destroy']);

    Route::get('/rewards', [RewardController::class, 'index']);
    Route::get('/rewards/{reward}', [RewardController::class, 'show']);

    Route::post('/rewards', [RewardController::class, 'store']);
    Route::put('/rewards/{reward}', [RewardController::class, 'update']);
    Route::delete('/rewards/{reward}', [RewardController::class, 'destroy']);

    Route::get('/reward-orders', [RewardOrderController::class, 'index']);
    Route::post('/reward-orders', [RewardOrderController::class, 'store']);
    Route::put('/reward-orders/{rewardOrder}', [RewardOrderController::class, 'update']);
    Route::delete('/reward-orders/{rewardOrder}', [RewardOrderController::class, 'destroy']);

});