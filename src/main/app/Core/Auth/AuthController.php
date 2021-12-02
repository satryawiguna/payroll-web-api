<?php
/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

namespace App\Core\Auth;

use App\Core\Controllers\AbstractController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends AbstractController
{
    function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     */
    function login(Request $request): JsonResponse
    {
        $credentials = request(['email', 'password']);
        $credentials['is_active'] = 1;
        $remember = $request->get('remember', '0') === '1';

        if (!$token = auth()->attempt($credentials, $remember)) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return $this->jsonResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the authenticated User.
     */
    function me(): JsonResponse
    {
        return $this->jsonResponse(current_user());
    }

    /**
     * Log the user out (Invalidate the token).
     */
    function logout(): JsonResponse
    {
        auth()->logout();

        return $this->jsonResponse(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     */
    function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }
}
