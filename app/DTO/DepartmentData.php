<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * DepartmentData – Data Transfer Object for department input.
 *
 * WHY DTOs EXIST:
 *   Instead of passing raw arrays from controllers to services,
 *   DTOs provide type-safe, validated input objects.
 *   Benefits:
 *     1. IDE autocomplete for all properties
 *     2. Type errors caught at construction, not buried in service logic
 *     3. Self-documenting method signatures
 *
 * LARAVEL INTEGRATION:
 *   DTOs are constructed from FormRequest->validated() in the controller.
 *   The Service layer accepts DTOs, not arrays, enforcing the contract.
 *
 * PHP 8 FEATURES:
 *   - Constructor property promotion
 *   - Readonly properties (data is immutable once created)
 */
final class DepartmentData
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description = null,
    ) {}

    /**
     * Create a DepartmentData from a validated array (e.g., from FormRequest).
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            name:        $validated['name'],
            code:        strtoupper($validated['code']),
            description: $validated['description'] ?? null,
        );
    }

    /**
     * Convert back to array for repository create/update calls.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'code'        => $this->code,
            'description' => $this->description,
        ];
    }
}
