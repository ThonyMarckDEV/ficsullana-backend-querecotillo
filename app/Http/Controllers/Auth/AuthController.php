<?php

namespace App\Http\Controllers\Auth;

//VALIDACIONES
use App\Http\Controllers\Auth\services\PasswordResetService;


//SERVICIOS
use App\Http\Controllers\Auth\services\TokenService;
use App\Http\Controllers\Auth\utilities\AuthValidations;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetEmail;
use App\Models\User;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    /**
     * Handle user login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate request
        $validator = AuthValidations::validateLogin($request);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Find user
        $user = User::with(['rol', 'datos.contactos'])->where('username', $request->username)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Usuario o contraseña incorrectos',
            ], 401);
        }

        // Check user status
        if ($user->estado !== 1) {
            return response()->json([
                'message' => 'Error: estado del usuario inactivo',
            ], 403);
        }

        // Check for existing reset token or password matching DNI for clients (idRol = 3)
        if ($user->idRol === 3) {
            // Check existing reset token
            $existingReset = PasswordResetService::checkExistingResetToken($user);
            if ($existingReset) {
                return response()->json([
                    'message' => $existingReset['message'],
                ], $existingReset['success'] ? 403 : 400);
            }

            // Check if password matches DNI
            $dni = $user->datos->dni ?? '';
            if ($request->password === $dni) {
                $resetResult = PasswordResetService::handlePasswordReset($user, $request->ip(), $request->userAgent());
                return response()->json([
                    'message' => $resetResult['message'],
                ], $resetResult['success'] ? 403 : 400);
            }
        }

        // Generate tokens
        $tokens = TokenService::generateTokens($user, $request->remember_me ?? false, $request->ip(), $request->userAgent());

        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'idRefreshToken' => $tokens['idRefreshToken'],
        ], 200);
    }

    /**
     * Refresh access token using refresh token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        // Validate request
        $validator = AuthValidations::validateRefreshToken($request);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Refresh token inválido',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Decode refresh token
            $secret = config('jwt.secret');
            $payload = JWT::decode($request->refresh_token, new Key($secret, 'HS256'));

            // Verify token type
            if (!isset($payload->type) || $payload->type !== 'refresh') {
                return response()->json([
                    'message' => 'El token proporcionado no es un token de refresco',
                ], 401);
            }

            // Find user
            $user = User::with(['rol', 'datos.contactos'])->find($payload->sub);
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado',
                ], 404);
            }

            // Generate new access token
            $tokens = TokenService::generateTokens($user, false, $request->ip(), $request->userAgent());

            return response()->json([
                'message' => 'Token actualizado',
                'access_token' => $tokens['access_token'],
                'token_type' => 'bearer',
                'expires_in' => $tokens['expires_in'],
            ], 200);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'message' => 'Refresh token expirado',
            ], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json([
                'message' => 'Refresh token inválido',
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar el token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate refresh token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateRefreshToken(Request $request)
    {
        // Validate request
        $validator = AuthValidations::validateRefreshTokenValidation($request);
        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Check refresh token in database
            $refreshToken = DB::table('refresh_tokens')
                ->where('idToken', $request->refresh_token_id)
                ->where('idUsuario', $request->userID)
                ->first();

            if (!$refreshToken) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Token no válido o no autorizado',
                ], 401);
            }

            // Check if token has expired
            if ($refreshToken->expires_at && now()->greaterThan($refreshToken->expires_at)) {
                DB::table('refresh_tokens')
                    ->where('idToken', $request->refresh_token_id)
                    ->where('idUsuario', $request->userID)
                    ->delete();

                return response()->json([
                    'valid' => false,
                    'message' => 'Token expirado',
                ], 401);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Token válido',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error validating refresh token: ' . $e->getMessage());
            return response()->json([
                'valid' => false,
                'message' => 'Error al validar el token',
            ], 500);
        }
    }

    /**
     * Handle user logout.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Validate request
        $validator = AuthValidations::validateLogout($request);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Delete refresh token
        $deleted = DB::table('refresh_tokens')
            ->where('idToken', $request->idToken)
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'OK',
            ], 200);
        }

        return response()->json([
            'message' => 'Error: No se encontró el token de refresco',
        ], 404);
    }
}