<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\CompanyTokenService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $user = $request->user();

        $accessToken = app(CompanyTokenService::class)->issue(
            $user,
            $request->input('client_type'),
            $request->input('device_id'),
        );

        return response()->json([
            'token' => $accessToken->plainTextToken,
            'user_id' => $user->id,
            'tenant_id' => $user->tenant?->id,
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
        $request->user()->currentAccessToken()?->delete();
    }
}
