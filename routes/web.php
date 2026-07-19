<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Ai\Enums\Lab;

use function Laravel\Ai\agent;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);

    Route::post('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::resource('employees', EmployeeController::class);

    Route::post('leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leave_request}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
    Route::resource('leave-requests', LeaveRequestController::class);

    // FAQ Routes – semantic search must be registered before the resource
    // so it is not caught by the {faq} show binding.
    Route::get('faqs/search', [FaqController::class, 'search'])->name('faqs.search');
    Route::resource('faqs', FaqController::class);
});

Route::get('/ai-test', function () {
    $response = agent(
        instructions: 'You are a helpful assistant.'
    )->prompt(
        'Say Hello',
        provider: Lab::Gemini,
    );

    return [$response, 'jaga'];
});

require __DIR__.'/auth.php';
