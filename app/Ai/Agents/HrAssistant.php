<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\ListPendingLeaveRequestsTool;
use App\Ai\Tools\LookupEmployeeTool;
use App\Ai\Tools\SearchFaqTool;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\RemembersConversations as RemembersConversationsContract;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * HrAssistant – the core reusable HR agent for demos.
 *
 * Demonstrates: Agent as a reusable, named AI capability class.
 * Used in: /demo/ai/agent (basic), /demo/ai/tools (with tool trace).
 */
class HrAssistant implements Agent, Conversational, HasTools, RemembersConversationsContract
{
    use Promptable;
    use RemembersConversations;

    /**
     * Get the agent's system instructions.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You are a helpful HR assistant for an employee management system.
        You help managers and HR staff answer questions about employees,
        leave requests, and HR policies. Be concise, professional, and accurate.
        When using tools, prefer looking up real data before answering.
        INSTRUCTIONS;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        if ($this->conversationId) {
            return resolve(ConversationStore::class)
                ->getLatestConversationMessages(
                    $this->conversationId,
                    $this->maxConversationMessages()
                )->all();
        }

        return [
            new UserMessage('What is our remote work policy?'),
            new AssistantMessage('Employees are allowed up to 2 days of remote work per week with manager approval.'),
        ];
    }

    /**
     * Get the tools available to this agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new LookupEmployeeTool,
            new ListPendingLeaveRequestsTool,
            new SearchFaqTool,
        ];
    }
}
