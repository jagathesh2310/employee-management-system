<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\RemembersConversations as RemembersConversationsContract;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * ChatAgent – multi-turn conversational agent.
 *
 * Demonstrates: Persistent multi-turn conversations via RemembersConversations.
 * Used in: /demo/ai/chat.
 */
class ChatAgent implements Agent, Conversational, HasTools, RemembersConversationsContract
{
    use Promptable;
    use RemembersConversations;

    /**
     * Get the agent's system instructions.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You are a knowledgeable HR assistant with memory of previous messages in this conversation.
        You can answer questions about HR policies, employees, and leave requests.
        Always refer back to earlier parts of the conversation when relevant to show continuity.
        If asked a follow-up, explicitly acknowledge what was discussed earlier.
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
}
