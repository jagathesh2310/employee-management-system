<?php

declare(strict_types=1);

use App\Ai\Agents\HrAssistant;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can view tools page', function () {
    $this->actingAs($this->user)
        ->get(route('demo.ai.tools'))
        ->assertOk()
        ->assertSee('Tools');
});

test('tools prompt returns response with tool_calls array', function () {
    HrAssistant::fake(['There are 3 pending leave requests.']);

    $this->actingAs($this->user)
        ->postJson(route('demo.ai.tools.prompt'), [
            'prompt' => 'List pending leave requests',
        ])
        ->assertOk()
        ->assertJsonStructure(['text', 'tool_calls', 'tool_results', 'elapsed_ms'])
        ->assertJsonFragment(['text' => 'There are 3 pending leave requests.']);
});

test('tools prompt validates required fields', function () {
    $this->actingAs($this->user)
        ->postJson(route('demo.ai.tools.prompt'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['prompt']);
});
