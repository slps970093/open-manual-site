<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;

// Frontend routes
Route::get('/', [FrontendController::class, 'index'])->name('frontend.index');
Route::get('/manual/{slug}', [FrontendController::class, 'manual'])->name('frontend.manual');
Route::get('/page/{id}', [FrontendController::class, 'page'])->name('frontend.page');
Route::get('/search', [FrontendController::class, 'search'])->name('frontend.search');
