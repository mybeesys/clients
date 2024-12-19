<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $user = User::where('email', $request->email)->first();
        $tenant = $user->tenant;
        $user->tokens()->delete();

        return response()->json([
            'token' => $user->createToken($user->email)->plainTextToken,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id
        ]);
    }

    public function verifyToken(Request $request)
    {
        $user = User::find($request->user()->id);

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
        return response()->json(['message' => 'Token is valid'], 200);
    }

    public function destroy(Request $request)
    {
        $request->user()->tokens()->delete();
    }
}
