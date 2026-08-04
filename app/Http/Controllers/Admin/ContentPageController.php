<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Platform\Models\ContentPage;
use App\Domain\Platform\Services\ImageUploadService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentPageController extends Controller
{
    public function __construct(private readonly ImageUploadService $imageUploadService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ContentPage::class);

        return Inertia::render('Dashboard/Admin/Content/Pages/Index', [
            'pages' => ContentPage::query()->orderBy('group')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ContentPage::class);

        $validated = $this->validated($request);

        $page = ContentPage::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        if ($request->hasFile('image')) {
            $this->attachImage($page, $request);
        }

        return back()->with('success', 'Konten berhasil ditambahkan.');
    }

    public function update(Request $request, ContentPage $contentPage): RedirectResponse
    {
        $this->authorize('update', $contentPage);

        $validated = $this->validated($request, $contentPage);

        $contentPage->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        if ($request->hasFile('image')) {
            $this->attachImage($contentPage, $request);
        }

        return back()->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroy(Request $request, ContentPage $contentPage): RedirectResponse
    {
        $this->authorize('delete', $contentPage);

        $contentPage->delete();

        return back()->with('success', 'Konten berhasil dihapus.');
    }

    private function validated(Request $request, ?ContentPage $ignoring = null): array
    {
        return $request->validate([
            'group' => ['required', 'string', 'max:50'],
            'key' => [
                'nullable', 'string', 'max:100',
                function ($attribute, $value, $fail) use ($request, $ignoring) {
                    if (! $value) {
                        return;
                    }
                    $exists = ContentPage::query()
                        ->where('group', $request->input('group'))
                        ->where('key', $value)
                        ->when($ignoring, fn ($q) => $q->whereKeyNot($ignoring->id))
                        ->exists();
                    if ($exists) {
                        $fail('Kombinasi grup dan key sudah digunakan.');
                    }
                },
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_published' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);
    }

    private function attachImage(ContentPage $page, Request $request): void
    {
        $this->imageUploadService->delete($page->image_path);

        $result = $this->imageUploadService->upload($request->file('image'), 'content-pages', maxWidth: 2400);

        $page->update(['image_path' => $result['path']]);
    }
}
