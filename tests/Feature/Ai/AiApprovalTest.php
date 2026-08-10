<?php

declare(strict_types=1);

use App\Ai\Agents\ApprovalAgent;
use App\Enums\ApprovalStatus;
use App\Models\AiApprovalProposal;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('authenticated user can view approval page', function () {
    $this->actingAs($this->user)
        ->get(route('demo.ai.approval'))
        ->assertOk()
        ->assertSee('Human Approval');
});

test('proposing creates a pending proposal', function () {
    ApprovalAgent::fake([
        json_encode([
            'question' => 'What is the remote work policy?',
            'answer' => 'Employees may work remotely up to 2 days per week with manager approval.',
        ]),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('demo.ai.approval.propose'), [
            'topic' => 'Remote work policy',
        ])
        ->assertOk()
        ->assertJsonStructure(['proposal_id', 'proposed']);

    $proposalId = $response->json('proposal_id');
    $proposal = AiApprovalProposal::find($proposalId);

    expect($proposal)->not->toBeNull();
    expect($proposal->status)->toBe(ApprovalStatus::Pending);
    expect($proposal->proposed_by)->toBe($this->user->id);
});

test('approving a proposal creates a FAQ', function () {
    $proposal = AiApprovalProposal::create([
        'topic' => 'Test topic',
        'payload' => [
            'question' => 'What is the overtime policy?',
            'answer' => 'Overtime must be pre-approved by the line manager.',
        ],
        'status' => 'pending',
        'proposed_by' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->postJson(route('demo.ai.approval.approve', $proposal))
        ->assertOk()
        ->assertJsonFragment(['message' => 'Approved! FAQ has been created.']);

    expect(AiApprovalProposal::find($proposal->id)->status)->toBe(ApprovalStatus::Approved);

    $this->assertDatabaseHas('faqs', [
        'question' => 'What is the overtime policy?',
    ]);
});

test('rejecting a proposal does not create a FAQ', function () {
    $proposal = AiApprovalProposal::create([
        'topic' => 'Sensitive topic',
        'payload' => [
            'question' => 'Draft question?',
            'answer' => 'Draft answer.',
        ],
        'status' => 'pending',
        'proposed_by' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->postJson(route('demo.ai.approval.reject', $proposal))
        ->assertOk()
        ->assertJsonFragment(['message' => 'Rejected. No FAQ was created.']);

    expect(AiApprovalProposal::find($proposal->id)->status)->toBe(ApprovalStatus::Rejected);

    $this->assertDatabaseMissing('faqs', [
        'question' => 'Draft question?',
    ]);
});

test('already resolved proposal cannot be approved again', function () {
    $proposal = AiApprovalProposal::create([
        'topic' => 'Already done',
        'payload' => ['question' => 'Q?', 'answer' => 'A.'],
        'status' => 'approved',
        'proposed_by' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->postJson(route('demo.ai.approval.approve', $proposal))
        ->assertUnprocessable()
        ->assertJsonFragment(['error' => 'Proposal already resolved.']);
});
