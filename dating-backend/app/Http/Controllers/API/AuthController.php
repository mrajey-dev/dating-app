<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;   // ✅ THIS LINE IS REQUIRED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
    'token' => $user->createToken('auth_token')->plainTextToken,
    'user'  => $user
]);

    }
}
