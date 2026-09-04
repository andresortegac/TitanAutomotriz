<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index', ['users' => User::latest()->paginate(10)]);
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        User::create($this->validated($request));

        return redirect()->route('users.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('No puedes eliminar tu propio usuario.');
        }

        if ($user->sales()->exists()) {
            $user->update(['active' => false]);

            return back()->with('success', 'Usuario desactivado porque tiene ventas registradas.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $passwordRules = $user ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'vendedor'])],
            'active' => ['nullable', 'boolean'],
            'password' => $passwordRules,
        ]) + ['active' => false];
    }
}
