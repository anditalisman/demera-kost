<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Platform\Models\Faq;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Faq::class);

        return Inertia::render('Dashboard/Admin/Content/Faqs/Index', [
            'faqs' => Faq::query()->orderBy('category')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Faq::class);

        Faq::create($this->validated($request));

        return back()->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $this->authorize('update', $faq);

        $faq->update($this->validated($request));

        return back()->with('success', 'FAQ berhasil diperbarui.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $this->authorize('delete', $faq);

        $faq->delete();

        return back()->with('success', 'FAQ berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:5000'],
            'category' => ['required', 'string', 'max:50'],
            'is_published' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);
    }
}
