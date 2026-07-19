<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SearchFaqRequest;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Models\Faq;
use App\Services\FaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __construct(
        private readonly FaqService $faqService,
    ) {}

    /**
     * Display a paginated listing of FAQs.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'sort_by', 'sort_dir', 'status']);
        $faqs = $this->faqService->getPaginated($filters);

        return view('faqs.index', compact('faqs', 'filters'));
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create(): View
    {
        return view('faqs.create');
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(StoreFaqRequest $request): RedirectResponse
    {
        $this->faqService->create($request->validated());

        return redirect()->route('faqs.index')->with('success', 'FAQ created successfully. Embedding will be generated shortly.');
    }

    /**
     * Display the specified FAQ.
     */
    public function show(Faq $faq): View
    {
        return view('faqs.show', compact('faq'));
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(Faq $faq): View
    {
        return view('faqs.edit', compact('faq'));
    }

    /**
     * Update the specified FAQ.
     */
    public function update(UpdateFaqRequest $request, Faq $faq): RedirectResponse
    {
        $this->faqService->update($faq, $request->validated());

        return redirect()->route('faqs.index')->with('success', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $this->faqService->delete($faq);

        return redirect()->route('faqs.index')->with('success', 'FAQ deleted successfully.');
    }

    /**
     * Perform a semantic similarity search across FAQs.
     */
    public function search(SearchFaqRequest $request): View
    {
        $results = collect();
        $validated = $request->safe()->only(['query', 'limit']);
        $queryText = $validated['query'] ?? '';
        $limit = (int) ($validated['limit'] ?? 5);

        if ($request->filled('query')) {
            $results = $this->faqService->semanticSearch($queryText, $limit);
        }

        return view('faqs.search', compact('results', 'queryText', 'limit'));
    }
}
