<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceUser;
use App\Models\Role;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {

        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','lowercase','email','max:255','unique:'.User::class],
            'password' => ['required','confirmed', Rules\Password::defaults()],
            'workspace' => ['required','string','max:255'],
        ]);

        DB::transaction(function () use ($request, &$user) {

            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->string('password')),
            ]);

            // Crear workspace (promotoría)
            $workspace = Workspace::create([
                'nombre' => $request->workspace,
                'slug' => Str::slug($request->workspace),
                'owner_id' => $user->id
            ]);

            // Buscar rol admin
            $adminRole = Role::where('nombre','admin')->first();

            // Relacionar usuario con workspace
            WorkspaceUser::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role_id' => $adminRole->id
            ]);

        });

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}