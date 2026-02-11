<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;

// Frontend routes
// Manual list page (home)
Route::get('/', [FrontendController::class, 'index'])->name('frontend.manuals');

// Manual detail page
Route::get('/manuals/{slug}', [FrontendController::class, 'manual'])->name('frontend.manual');

// Manual page content
Route::get('/manuals/page/{id}', [FrontendController::class, 'page'])->name('frontend.page');

// Search
Route::get('/search', [FrontendController::class, 'search'])->name('frontend.search');
