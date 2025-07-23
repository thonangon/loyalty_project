<?php

use App\Http\Controllers\BlogPostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogPostController::class, 'index']);
