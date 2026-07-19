<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property bool $is_active
 * @property string|null $embedding Raw vector string stored as text (pgvector casts it)
 * @property Carbon|null $embedding_generated_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'is_active',
        'embedding',
        'embedding_generated_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_active' => 'boolean',
        'embedding_generated_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to only active FAQs.
     *
     * @param  Builder<Faq>  $query
     * @return Builder<Faq>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only FAQs that have an embedding generated.
     *
     * @param  Builder<Faq>  $query
     * @return Builder<Faq>
     */
    public function scopeWithEmbedding(Builder $query): Builder
    {
        return $query->whereNotNull('embedding');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Whether this FAQ already has a vector embedding.
     */
    public function hasEmbedding(): bool
    {
        return $this->embedding !== null;
    }
}
