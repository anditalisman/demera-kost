<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Property;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Property::class);

        return Inertia::render('Dashboard/Admin/Living/Properties/Index', [
            'properties' => Property::query()
                ->with(['buildings.floors'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Property::class);

        $validated = $this->validated($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        Property::create($validated);

        return back()->with('success', 'Properti berhasil ditambahkan.');
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $property->update($this->validated($request));

        return back()->with('success', 'Properti berhasil diperbarui.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        $property->delete();

        return back()->with('success', 'Properti berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
            'house_rules' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_whatsapp' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Property::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
