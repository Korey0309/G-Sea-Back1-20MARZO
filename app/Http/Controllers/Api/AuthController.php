<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inicio de sesión solo API: devuelve token Sanctum (sin sesión ni CSRF).
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ensureLoginNotRateLimited($request);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($this->loginThrottleKey($request));

             $device = $request->input('device_name', 'web');

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        RateLimiter::clear($this->loginThrottleKey($request));

        $device = $credentials['device_name'] ?? 'api';
        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Registro igual que el flujo web (workspace + rol admin), pero respuesta API con token.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'workspace' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = null;

        DB::transaction(function () use ($data, &$user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $workspace = Workspace::create([
                'nombre' => $data['workspace'],
                'slug' => Str::slug($data['workspace']).'-'.Str::random(6),
                'owner_id' => $user->id,
            ]);

            $adminRole = Role::firstOrCreate(['nombre' => 'admin']);

            WorkspaceUser::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role_id' => $adminRole->id,
            ]);
        });

        event(new Registered($user));

        $device = $data['device_name'] ?? 'api';
        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'user' => $user->load('workspaces'),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Sesión de token cerrada.']);
    }

    private function ensureLoginNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->loginThrottleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->loginThrottleKey($request));

        throw ValidationException::withMessages([
            'email' => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ]);
    }

    private function loginThrottleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }
}
