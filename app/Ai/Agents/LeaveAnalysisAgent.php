<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\RemembersConversations as RemembersConversationsContract;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * LeaveAnalysisAgent – structured-output agent for leave request analysis.
 *
 * Demonstrates: Structured output with a defined JSON schema rendered as UI cards.
 * Used in: /demo/ai/structured, /demo/ai/queue (queued version).
 */
class LeaveAnalysisAgent implements Agent, Conversational, HasStructuredOutput, HasTools, RemembersConversationsContract
{
    use Promptable;
    use RemembersConversations;

    /**
     * Get the agent's system instructions.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You are an expert HR analyst. When given a leave request, analyze it and
        return a structured response with:
        - A concise summary of the request and context
        - A recommended action (Approve, Reject, or Request More Info)
        - Your confidence level (0.0 to 1.0)
        - Citations (policy clauses or reasons supporting your recommendation)
        Be objective, fair, and base recommendations on standard HR policies.
        INSTRUCTIONS;
    }

    /**
     * Get the tools available to this agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Define the structured output schema.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()
                ->description('A 2-3 sentence summary of the leave request and relevant context.')
                ->required(),

            'recommended_action' => $schema->string()
                ->enum(['Approve', 'Reject', 'Request More Info'])
                ->description('The recommended HR action for this leave request.')
                ->required(),

            'confidence' => $schema->number()
                ->min(0)
                ->max(1)
                ->description('Confidence level in the recommendation, from 0.0 (low) to 1.0 (high).')
                ->required(),

            'citations' => $schema->array()
                ->items($schema->string())
                ->description('List of policy clauses, reasons, or precedents supporting the recommendation.')
                ->required(),
        ];
    }
}
