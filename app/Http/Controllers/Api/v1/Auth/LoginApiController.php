<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginApiController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password',
            ], 401);
        }

        $user = Auth::user();

        // Set cookie untuk auth_token dan user_role
        $cookie_token = cookie('auth_token', $token, 60 * 24 * 7, '/', null, false, true); // 7 hari
        $cookie_role = cookie('user_role', $user->role, 60 * 24 * 7, '/', null, false, false); // HttpOnly false agar JS bisa baca

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ])->cookie($cookie_token)
            ->cookie($cookie_role);
    }

    public function logout(Request $request)
    {
        try {
            // Invalidate JWT token
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // Token mungkin sudah invalid/expired
            \Log::warning('JWT invalidate failed: '.$e->getMessage());
        }

        // Hapus cookie dengan set expired
        $cookie_token = cookie('auth_token', '', -1, '/', null, false, true);
        $cookie_role = cookie('user_role', '', -1, '/', null, false, false);

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ])->cookie($cookie_token)
            ->cookie($cookie_role);
    }

    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            // kalau role nurse, ambil data dari tabel nurses
            $nurse = null;
            if ($user->role === 'nurse') {
                $nurse = $user->nurse;
            }
            $cashier = null;
            if ($user->role === 'cashier') {
                $cashier = $user->cashier;
            }

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'nurse' => $nurse,
                'cashier' => $cashier,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
    }
}
