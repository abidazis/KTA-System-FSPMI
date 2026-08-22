<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->latest()->paginate(20);

        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        $roles = Role::all();

        return view('users.create', ['roles' => $roles]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->syncRoles($validated['roles']);

        AuditLog::log('create_user', $user);

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User berhasil dibuat.');
    }

    public function show(User $user): View
    {
        $user->load('roles');

        return view('users.show', ['user' => $user]);
    }

    public function edit(User $user): View
    {
        $roles = Role::all();

        return view('users.edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $oldData = $user->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles($validated['roles']);

        AuditLog::log('update_user', $user, $oldData, $user->toArray());

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $oldData = $user->toArray();

        $user->delete();

        AuditLog::log('delete_user', null, $oldData, null);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
