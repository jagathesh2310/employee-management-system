<?php

declare(strict_types=1);

use App\Ai\Agents\HrAssistant;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guest is redirected from demo hub', function () {
    $this->get(route('demo.ai.hub'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view demo hub', function () {
    $this->actingAs($this->user)
        ->get(route('demo.ai.hub'))
        ->assertOk()
        ->assertSee('Laravel AI SDK')
        ->assertSee('Agent')
        ->assertSee('Tools')
        ->assertSee('Structured Output');
});

test('guest is redirected from agent page', function () {
    $this->get(route('demo.ai.agent'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view agent page', function () {
    $this->actingAs($this->user)
        ->get(route('demo.ai.agent'))
        ->assertOk()
        ->assertSee('HrAssistant');
});

test('agent prompt returns response', function () {
    HrAssistant::fake(['You have 18 days of annual leave per year.']);

    $this->actingAs($this->user)
        ->postJson(route('demo.ai.agent.prompt'), [
            'prompt' => 'How many days of annual leave do I get?',
        ])
        ->assertOk()
        ->assertJsonStructure(['text', 'elapsed_ms'])
        ->assertJsonFragment(['text' => 'You have 18 days of annual leave per year.']);

    HrAssistant::assertPrompted(fn ($p) => str_contains($p->prompt, 'annual leave'));
});

test('agent prompt validates required fields', function () {
    $this->actingAs($this->user)
        ->postJson(route('demo.ai.agent.prompt'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['prompt']);
});
