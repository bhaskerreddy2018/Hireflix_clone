<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [WebController::class, 'showLogin'])->name('login');
Route::post('/login', [WebController::class, 'login']);

Route::get('/register', [WebController::class, 'showRegister'])->name('register');
Route::post('/register', [WebController::class, 'register']);

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WebController::class, 'showDashboard'])->name('dashboard');
    Route::post('/logout', [WebController::class, 'logout'])->name('logout');
    
    // Interview management routes
    Route::post('/interviews', [WebController::class, 'createInterview'])->name('interviews.store');
    Route::get('/interviews', [WebController::class, 'getInterviews'])->name('interviews.index');
    
    // Candidate routes
    Route::get('/candidate/interviews', [WebController::class, 'getCandidateInterviews'])->name('candidate.interviews');
    Route::post('/submissions', [WebController::class, 'submitAnswer'])->name('submissions.store');
    
    // Reviewer routes
    Route::get('/reviewer/submissions', [WebController::class, 'getReviewerSubmissions'])->name('reviewer.submissions');
    Route::get('/reviewer/stats', [WebController::class, 'getReviewerStats'])->name('reviewer.stats');
    Route::post('/scores', [WebController::class, 'submitScore'])->name('scores.store');
    
    // Admin routes
    Route::get('/admin/stats', [WebController::class, 'getAdminStats'])->name('admin.stats');
    Route::get('/admin/candidates', [WebController::class, 'getAdminCandidates'])->name('admin.candidates');
    Route::get('/admin/reviewers', [WebController::class, 'getAdminReviewers'])->name('admin.reviewers');
    Route::get('/admin/submissions', [WebController::class, 'getAdminSubmissions'])->name('admin.submissions');
    Route::get('/admin/interviews/{id}', [WebController::class, 'getAdminInterviewDetails'])->name('admin.interviews.details');
});
