<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!Auth::attempt($cred, true)) { 
            return response()->json(['message' => 'Credenciales inválidas'], 422);
        }

        $request->session()->regenerate();

        return response()->json(['message' => 'ok'], 200);
    }

    public function me(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user) {
            return response()->json(null, 200);
        }

        $user->loadMissing('roles');

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,

            'roles'       => $user->getRoleNames(), 
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Sesión cerrada'], 200);
    }
}
