<?php

use App\Http\Controllers\Demo\AiAgentController;
use App\Http\Controllers\Demo\AiApprovalController;
use App\Http\Controllers\Demo\AiChatController;
use App\Http\Controllers\Demo\AiDemoHubController;
use App\Http\Controllers\Demo\AiFailoverController;
use App\Http\Controllers\Demo\AiFaqSearchController;
use App\Http\Controllers\Demo\AiQueueController;
use App\Http\Controllers\Demo\AiStreamController;
use App\Http\Controllers\Demo\AiStructuredController;
use App\Http\Controllers\Demo\AiSummarizeController;
use App\Http\Controllers\Demo\AiToolsController;
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

    // ─────────────────────────────────────────────────────────────────────────
    // AI SDK Demo Hub – all pages are browser UI, no console demos
    // ─────────────────────────────────────────────────────────────────────────
    Route::middleware('throttle:60,1')
        ->prefix('demo/ai')
        ->name('demo.ai.')
        ->group(function () {
            // Hub
            Route::get('/', [AiDemoHubController::class, 'index'])->name('hub');

            // 1. Basic Agent
            Route::get('/agent', [AiAgentController::class, 'show'])->name('agent');
            Route::post('/agent/prompt', [AiAgentController::class, 'prompt'])->name('agent.prompt');

            // 2. Tools + Tool Trace
            Route::get('/tools', [AiToolsController::class, 'show'])->name('tools');
            Route::post('/tools/prompt', [AiToolsController::class, 'prompt'])->name('tools.prompt');

            // 3. Structured Output
            Route::get('/structured', [AiStructuredController::class, 'show'])->name('structured');
            Route::post('/structured/prompt', [AiStructuredController::class, 'prompt'])->name('structured.prompt');

            // 4. Multi-turn Chat
            Route::get('/chat', [AiChatController::class, 'show'])->name('chat');
            Route::post('/chat/message', [AiChatController::class, 'message'])->name('chat.message');
            Route::post('/chat/reset', [AiChatController::class, 'reset'])->name('chat.reset');

            // 5. Streaming (SSE)
            Route::get('/stream', [AiStreamController::class, 'show'])->name('stream');
            Route::get('/stream/generate', [AiStreamController::class, 'stream'])->name('stream.generate');

            // 6. FAQ Semantic Compare
            Route::get('/faq-search', [AiFaqSearchController::class, 'show'])->name('faq-search');
            Route::get('/faq-search/search', [AiFaqSearchController::class, 'search'])->name('faq-search.search');

            // 7. Queued AI Analysis
            Route::get('/queue', [AiQueueController::class, 'show'])->name('queue');
            Route::post('/queue/analyze', [AiQueueController::class, 'analyze'])->name('queue.analyze');
            Route::get('/queue/status', [AiQueueController::class, 'status'])->name('queue.status');

            // 8. Human-in-the-loop Approval
            Route::get('/approval', [AiApprovalController::class, 'show'])->name('approval');
            Route::post('/approval/propose', [AiApprovalController::class, 'propose'])->name('approval.propose');
            Route::post('/approval/{aiApprovalProposal}/approve', [AiApprovalController::class, 'approve'])->name('approval.approve');
            Route::post('/approval/{aiApprovalProposal}/reject', [AiApprovalController::class, 'reject'])->name('approval.reject');

            // 9. Summarize
            Route::get('/summarize', [AiSummarizeController::class, 'show'])->name('summarize');
            Route::post('/summarize/run', [AiSummarizeController::class, 'summarize'])->name('summarize.run');

            // 10. Failover Config
            Route::get('/failover', [AiFailoverController::class, 'show'])->name('failover');
        });
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
