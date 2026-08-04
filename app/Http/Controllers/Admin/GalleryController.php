<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Platform\Models\Gallery;
use App\Domain\Platform\Services\ImageUploadService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function __construct(private readonly ImageUploadService $imageUploadService) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Gallery::class);

        return Inertia::render('Dashboard/Admin/Content/Galleries/Index', [
            'galleries' => Gallery::query()->orderBy('category')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Gallery::class);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'caption' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:8192'],
            'is_published' => ['boolean'],
        ]);

        $uploaded = $this->imageUploadService->upload($request->file('image'), 'galleries');

        $maxSort = Gallery::query()->where('category', $validated['category'])->max('sort_order') ?? 0;

        Gallery::create([
            ...collect($validated)->except('image')->toArray(),
            'image_path' => $uploaded['path'],
            'thumbnail_path' => $uploaded['thumbnail_path'],
            'sort_order' => $maxSort + 1,
            'uploaded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Foto berhasil ditambahkan ke galeri.');
    }

    public function update(Request $request, Gallery $gallery): RedirectResponse
    {
        $this->authorize('update', $gallery);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'caption' => ['nullable', 'string', 'max:255'],
            'is_published' => ['boolean'],
        ]);

        $gallery->update($validated);

        return back()->with('success', 'Galeri berhasil diperbarui.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->authorize('create', Gallery::class);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:galleries,id'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Gallery::whereKey($id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Urutan galeri berhasil disimpan.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $this->authorize('delete', $gallery);

        $this->imageUploadService->delete($gallery->image_path, $gallery->thumbnail_path);
        $gallery->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
