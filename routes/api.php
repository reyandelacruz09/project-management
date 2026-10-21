<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user'])->name('api.user');
    Route::get('users', [UserController::class, 'index'])->name('api.users.index');
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::apiResource('projects', ProjectController::class)->names('api.projects');
    Route::apiResource('projects.tasks', TaskController::class)->names('api.projects.tasks');
    Route::apiResource('teams', TeamController::class)->names('api.teams');

    Route::get('comments', [CommentController::class, 'index'])->name('api.comments.index');
    Route::post('comments', [CommentController::class, 'store'])->name('api.comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('api.comments.destroy');
});
