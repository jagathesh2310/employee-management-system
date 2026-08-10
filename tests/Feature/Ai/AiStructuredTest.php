<?php

declare(strict_types=1);

use App\Ai\Agents\LeaveAnalysisAgent;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can view structured page', function () {
    $this->actingAs($this->user)
        ->get(route('demo.ai.structured'))
        ->assertOk()
        ->assertSee('Structured Output');
});

test('structured prompt returns schema fields', function () {
    LeaveAnalysisAgent::fake([
        json_encode([
            'summary' => 'Employee requests medical leave for 10 days with doctor recommendation.',
            'recommended_action' => 'Approve',
            'confidence' => 0.9,
            'citations' => ['Policy §3.2: Medical leave with documentation is approvable.'],
        ]),
    ]);

    $this->actingAs($this->user)
        ->postJson(route('demo.ai.structured.prompt'), [
            'prompt' => 'Sarah has a doctor note for 10 days of medical leave.',
        ])
        ->assertOk()
        ->assertJsonPath('structured.recommended_action', 'Approve')
        ->assertJsonPath('structured.confidence', 0.9)
        ->assertJsonStructure([
            'structured' => ['summary', 'recommended_action', 'confidence', 'citations'],
            'elapsed_ms',
        ]);
});

test('structured prompt validates required fields', function () {
    $this->actingAs($this->user)
        ->postJson(route('demo.ai.structured.prompt'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['prompt']);
});
