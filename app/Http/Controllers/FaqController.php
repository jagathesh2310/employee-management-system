<?php

namespace App\Http\Controllers;

use App\Contracts\FaqRepositoryInterface;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(
        public FaqRepositoryInterface $faqRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $faqs = $this->faqRepository->paginate($request->all());

        return response()->json($faqs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $faq = $this->faqRepository->create($validated);

        return response()->json($faq, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq $faq)
    {
        return response()->json($faq);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'sometimes|required|string|max:255',
            'answer' => 'sometimes|required|string',
            'is_active' => 'boolean',
        ]);

        $faq = $this->faqRepository->update($faq, $validated);

        return response()->json($faq);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {
        $this->faqRepository->delete($faq);

        return response()->json(null, 204);
    }
}
