<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\PositionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * API Routes – Version 1
 *
 * LARAVEL FEATURES:
 *   - Route prefixes (v1)
 *   - Route groups with middleware
 *   - API Resource routes (apiResource creates index, store, show, update, destroy)
 *   - Scoped route models (implicitly resolved via route keys)
 */

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// v1 API Group protected by Sanctum
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // ---------------------------------------------------------
    // Departments
    // ---------------------------------------------------------
    Route::apiResource('departments', DepartmentController::class);

    // ---------------------------------------------------------
    // Positions
    // ---------------------------------------------------------
    Route::apiResource('positions', PositionController::class);

    // ---------------------------------------------------------
    // Employees
    // ---------------------------------------------------------
    // Custom export route MUST be defined before apiResource, otherwise 
    // the 'export' part of the URL is treated as the {employee} ID parameter.
    Route::post('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::apiResource('employees', EmployeeController::class);

    // ---------------------------------------------------------
    // Leave Requests
    // ---------------------------------------------------------
    // Custom action routes
    Route::post('leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])
        ->name('leave-requests.approve');
    Route::post('leave-requests/{leave_request}/reject', [LeaveRequestController::class, 'reject'])
        ->name('leave-requests.reject');
        
    Route::apiResource('leave-requests', LeaveRequestController::class);

});
