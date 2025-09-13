<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\ScoreController;

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

define('AUTH_SANCTUM', 'auth:sanctum');

// Public routes
Route::group(['prefix' => 'auth', 'controller' => AuthController::class], function () {
    Route::post('/login', ['as' => 'login', 'uses' => 'login']);
    Route::post('/register', 'register');
    Route::post('/logout', ['middleware' => AUTH_SANCTUM,'as' => 'logout', 'uses' => 'logout']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Interview routes
    Route::apiResource('interviews', InterviewController::class);
    
    // Submission routes
    Route::apiResource('submissions', SubmissionController::class);
    
    // Score routes
    Route::apiResource('scores', ScoreController::class);
    Route::get('/submissions/{submission}/scores', [ScoreController::class, 'getSubmissionScores']);
    Route::get('/reviewer/stats', [ScoreController::class, 'getReviewerStats']);
});
