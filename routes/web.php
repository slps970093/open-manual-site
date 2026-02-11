<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;

// Frontend routes
Route::get('/', function () {
    return view("home.index");
});
