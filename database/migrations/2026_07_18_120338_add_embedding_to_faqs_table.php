<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Uses raw SQL for the vector column and index because Laravel's Blueprint
     * does not natively know the pgvector `vector` type. The extension must be
     * available (pgvector/pgvector:pg18 Docker image provides it).
     *
     * 768 dimensions matches Gemini text-embedding-004 output size.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        // Add the native vector column and tracking timestamp via raw DDL.
        DB::statement('ALTER TABLE faqs ADD COLUMN IF NOT EXISTS embedding vector(768)');
        DB::statement('ALTER TABLE faqs ADD COLUMN IF NOT EXISTS embedding_generated_at timestamptz');

        // ivfflat approximate-nearest-neighbour index using cosine distance.
        // Tune `lists` based on row count (sqrt(rows) is a good starting point).
        DB::statement('
            CREATE INDEX IF NOT EXISTS faqs_embedding_ivfflat_idx
            ON faqs
            USING ivfflat (embedding vector_cosine_ops)
            WITH (lists = 10)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS faqs_embedding_ivfflat_idx');
        DB::statement('ALTER TABLE faqs DROP COLUMN IF EXISTS embedding_generated_at');
        DB::statement('ALTER TABLE faqs DROP COLUMN IF EXISTS embedding');
    }
};
