<?php

declare(strict_types=1);

use App\Jobs\AnalyzeLeaveRequestJob;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can view queue page', function () {
    $this->actingAs($this->user)
        ->get(route('demo.ai.queue'))
        ->assertOk();
});

test('analyze endpoint dispatches job and returns cache key', function () {
    Queue::fake();

    $department = Department::factory()->create();
    $position = Position::factory()->create();
    $employee = Employee::factory()->create([
        'department_id' => $department->id,
        'position_id' => $position->id,
    ]);
    $leaveRequest = LeaveRequest::factory()->pending()->create([
        'employee_id' => $employee->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('demo.ai.queue.analyze'), [
            'leave_request_id' => $leaveRequest->id,
        ])
        ->assertOk()
        ->assertJsonStructure(['cache_key', 'message']);

    Queue::assertPushed(AnalyzeLeaveRequestJob::class);

    $cacheKey = $response->json('cache_key');
    expect(Cache::get($cacheKey))->not->toBeNull();
    expect(Cache::get($cacheKey)['status'])->toBe('queued');
});

test('status endpoint returns cache data', function () {
    $cacheKey = 'test_key_'.time();
    Cache::put($cacheKey, ['status' => 'done', 'result' => ['summary' => 'All good']], 60);

    $this->actingAs($this->user)
        ->getJson(route('demo.ai.queue.status', ['cache_key' => $cacheKey]))
        ->assertOk()
        ->assertJsonPath('status', 'done');
});

test('status endpoint returns not_found for missing key', function () {
    $this->actingAs($this->user)
        ->getJson(route('demo.ai.queue.status', ['cache_key' => 'nonexistent_key_xyz']))
        ->assertOk()
        ->assertJsonPath('status', 'not_found');
});
