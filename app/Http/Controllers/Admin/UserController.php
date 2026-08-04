<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    private const ASSIGNABLE_ROLES = ['super-admin', 'admin', 'property-manager', 'finance', 'customer', 'tenant'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles:id,name')
            ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('whatsapp_number', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dashboard/Admin/Users/Index', [
            'users' => $users,
            'roles' => self::ASSIGNABLE_ROLES,
            'filters' => $request->only('search'),
        ]);
    }

    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manageRoles', $user);

        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::in(self::ASSIGNABLE_ROLES)],
        ]);

        $user->syncRoles($validated['roles']);

        return back()->with('success', "Role untuk {$user->name} berhasil diperbarui.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menonaktifkan akun sendiri.');

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return back()->with('success', $user->is_active ? "{$user->name} diaktifkan kembali." : "{$user->name} dinonaktifkan.");
    }
}
