<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Platform\Models\Testimonial;
use App\Domain\Platform\Services\ImageUploadService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function __construct(private readonly ImageUploadService $imageUploadService) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Testimonial::class);

        return Inertia::render('Dashboard/Admin/Content/Testimonials/Index', [
            'testimonials' => Testimonial::query()->orderByDesc('is_featured')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Testimonial::class);

        $validated = $this->validated($request);

        $testimonial = Testimonial::create([
            ...collect($validated)->except('photo')->toArray(),
        ]);

        if ($request->hasFile('photo')) {
            $uploaded = $this->imageUploadService->upload($request->file('photo'), 'testimonials', maxWidth: 600);
            $testimonial->update(['author_photo_path' => $uploaded['path']]);
        }

        return back()->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('update', $testimonial);

        $validated = $this->validated($request);

        $testimonial->update(collect($validated)->except('photo')->toArray());

        if ($request->hasFile('photo')) {
            $this->imageUploadService->delete($testimonial->author_photo_path);
            $uploaded = $this->imageUploadService->upload($request->file('photo'), 'testimonials', maxWidth: 600);
            $testimonial->update(['author_photo_path' => $uploaded['path']]);
        }

        return back()->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('delete', $testimonial);

        $this->imageUploadService->delete($testimonial->author_photo_path);
        $testimonial->delete();

        return back()->with('success', 'Testimoni berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'author_name' => ['required', 'string', 'max:150'],
            'author_role' => ['nullable', 'string', 'max:150'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string', 'max:2000'],
            'source' => ['required', 'in:living,fashion,general'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);
    }
}
