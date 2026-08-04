<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Facility;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Facility::class);

        return Inertia::render('Dashboard/Admin/Living/Facilities/Index', [
            'facilities' => Facility::query()->orderBy('type')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Facility::class);

        $validated = $this->validated($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        Facility::create($validated);

        return back()->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $this->authorize('update', $facility);

        $facility->update($this->validated($request));

        return back()->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        $this->authorize('delete', $facility);

        $facility->delete();

        return back()->with('success', 'Fasilitas berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:room,shared'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Facility::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
