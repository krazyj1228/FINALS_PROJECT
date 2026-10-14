<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'home'])->name('home');
Route::get('/templates/{template}/preview', [PortfolioController::class, 'previewTemplate'])
    ->whereIn('template', ['simple', 'modern', 'creative'])
    ->name('templates.preview');
Route::get('/portfolios/{portfolio}/full-view', [PortfolioController::class, 'fullView'])
    ->name('portfolios.full-view');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])
        ->middleware('throttle:5,1')
        ->name('login.authenticate');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::resource('portfolios', PortfolioController::class)
        ->only(['index', 'create', 'edit', 'update', 'destroy']);

    Route::post('/portfolios', [PortfolioController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('portfolios.store');

    Route::get('/portfolios/{portfolio}/templates', [PortfolioController::class, 'templates'])
        ->name('portfolios.templates');
    Route::get('/portfolios/{portfolio}/templates/{template}/preview', [PortfolioController::class, 'previewPortfolioTemplate'])
        ->whereIn('template', ['simple', 'modern', 'creative'])
        ->name('portfolios.templates.preview');
    Route::put('/portfolios/{portfolio}/template', [PortfolioController::class, 'setTemplate'])
        ->name('portfolios.template');
});

Route::get('/portfolios/{portfolio}', [PortfolioController::class, 'show'])
    ->name('portfolios.show');
