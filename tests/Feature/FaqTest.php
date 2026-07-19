<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateFaqEmbeddingJob;
use App\Models\Faq;
use App\Models\User;
use App\Services\FaqService;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable CSRF for all tests in this class (web form submissions in tests
        // do not include a CSRF token by default).
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_faq_index(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        Faq::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('faqs.index'));

        $response->assertOk();
        $response->assertViewIs('faqs.index');
        $response->assertViewHas('faqs');
    }

    public function test_guests_are_redirected_from_faq_index(): void
    {
        $response = $this->get(route('faqs.index'));
        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Create & Store
    // -------------------------------------------------------------------------

    public function test_admin_can_view_create_faq_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('faqs.create'));
        $response->assertOk();
        $response->assertViewIs('faqs.create');
    }

    public function test_admin_can_create_faq_and_embedding_job_is_dispatched(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('faqs.store'), [
            'question' => 'How do I reset my password?',
            'answer' => 'Click on the forgot password link on the login page and follow the instructions.',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('faqs.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faqs', ['question' => 'How do I reset my password?']);

        Queue::assertPushedOn('embeddings', GenerateFaqEmbeddingJob::class);
    }

    public function test_store_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('faqs.store'), []);

        $response->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_regular_user_cannot_create_faq(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($user)->post(route('faqs.store'), [
            'question' => 'A question with enough characters?',
            'answer' => 'An answer that is definitely long enough to pass validation here.',
            'is_active' => true,
        ]);

        $response->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_faq(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($user)->get(route('faqs.show', $faq));
        $response->assertOk();
        $response->assertViewIs('faqs.show');
        $response->assertSee($faq->question);
    }

    // -------------------------------------------------------------------------
    // Edit & Update
    // -------------------------------------------------------------------------

    public function test_admin_can_update_faq(): void
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($admin)->put(route('faqs.update', $faq), [
            'question' => 'Updated question text here?',
            'answer' => 'Updated answer that is definitely long enough to pass validation.',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('faqs.index'));
        $this->assertDatabaseHas('faqs', ['question' => 'Updated question text here?']);

        // Content changed → embedding job should be re-dispatched.
        Queue::assertPushedOn('embeddings', GenerateFaqEmbeddingJob::class);
    }

    public function test_regular_user_cannot_update_faq(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($user)->put(route('faqs.update', $faq), [
            'question' => 'Changed question with enough chars?',
            'answer' => 'Changed answer that is long enough.',
        ]);

        $response->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_admin_can_delete_faq(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($admin)->delete(route('faqs.destroy', $faq));

        $response->assertRedirect(route('faqs.index'));
        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_regular_user_cannot_delete_faq(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $faq = Faq::factory()->create();

        $response = $this->actingAs($user)->delete(route('faqs.destroy', $faq));

        $response->assertForbidden();
        $this->assertDatabaseHas('faqs', ['id' => $faq->id]);
    }

    // -------------------------------------------------------------------------
    // Semantic Search page
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_search_page_without_query(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($user)->get(route('faqs.search'));
        $response->assertOk();
        $response->assertViewIs('faqs.search');
    }

    // -------------------------------------------------------------------------
    // FaqService – unit-style via the service layer
    // -------------------------------------------------------------------------

    public function test_faq_service_delete_removes_record(): void
    {
        $faq = Faq::factory()->create();
        $service = app(FaqService::class);

        $service->delete($faq);

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }
}
