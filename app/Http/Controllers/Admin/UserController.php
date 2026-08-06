<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Platform\Services\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    private const ASSIGNABLE_ROLES = ['admin', 'customer'];

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

        $previousRoles = $user->getRoleNames()->all();

        $user->syncRoles($validated['roles']);

        AuditLogger::log(
            'role_changed',
            $user,
            old: ['roles' => $previousRoles],
            new: ['roles' => $validated['roles']],
            description: "Role {$user->name} diubah oleh ".auth()->user()->name,
        );

        return back()->with('success', "Role untuk {$user->name} berhasil diperbarui.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menonaktifkan akun sendiri.');

        $wasActive = $user->is_active;

        $user->forceFill(['is_active' => ! $wasActive])->save();

        AuditLogger::log(
            $wasActive ? 'user_deactivated' : 'user_activated',
            $user,
            description: "{$user->name} ".($wasActive ? 'dinonaktifkan' : 'diaktifkan')." oleh ".auth()->user()->name,
        );

        return back()->with('success', $user->is_active ? "{$user->name} diaktifkan kembali." : "{$user->name} dinonaktifkan.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $name = $user->name;

        $user->delete();

        AuditLogger::log(
            'user_deleted',
            $user,
            description: "{$name} dihapus oleh ".auth()->user()->name,
        );

        return back()->with('success', "{$name} berhasil dihapus.");
    }
}
