<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Models\Property;
use App\Domain\Living\Models\RoomType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoomTypeController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', RoomType::class);

        return Inertia::render('Dashboard/Admin/Living/RoomTypes/Index', [
            'roomTypes' => RoomType::query()->with('property')->orderBy('name')->get(),
            'properties' => Property::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RoomType::class);

        $validated = $this->validated($request);
        $validated['slug'] = $this->uniqueSlug($validated['property_id'], $validated['name']);

        RoomType::create($validated);

        return back()->with('success', 'Tipe kamar berhasil ditambahkan.');
    }

    public function update(Request $request, RoomType $roomType): RedirectResponse
    {
        $this->authorize('update', $roomType);

        $roomType->update($this->validated($request));

        return back()->with('success', 'Tipe kamar berhasil diperbarui.');
    }

    public function destroy(RoomType $roomType): RedirectResponse
    {
        $this->authorize('delete', $roomType);

        $roomType->delete();

        return back()->with('success', 'Tipe kamar berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'base_deposit' => ['required', 'numeric', 'min:0'],
            'size_sqm' => ['nullable', 'numeric', 'min:0'],
            'default_capacity' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function uniqueSlug(int $propertyId, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (RoomType::withTrashed()->where('property_id', $propertyId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
