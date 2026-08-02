<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * SummarizeAgent – single-purpose text summarization agent.
 *
 * Demonstrates: Focused agents for specific tasks.
 * Used in: /demo/ai/summarize.
 */
class SummarizeAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the agent's system instructions.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You are a concise summarization assistant. When given a block of text,
        produce a clear, concise summary in 2-4 bullet points. Each bullet
        should capture a key point. Do not include unnecessary filler words.
        Format your response as a plain bulleted list.
        INSTRUCTIONS;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
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
