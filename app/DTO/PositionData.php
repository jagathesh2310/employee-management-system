<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * PositionData DTO – type-safe position input transfer object.
 */
final class PositionData
{
    public function __construct(
        public readonly string $name,
        public readonly int $level,
        public readonly ?string $description = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name: $validated['name'],
            level: (int) $validated['level'],
            description: $validated['description'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'level' => $this->level,
            'description' => $this->description,
        ];
    }
}
