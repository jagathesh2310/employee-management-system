<?php

declare(strict_types=1);

use App\Http\Controllers\FaqController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Removed API Resource routes as they are now handled by web controllers and blade views.
Route::apiResource('faqs', FaqController::class);
