<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const GENERIC_LOGIN_ERROR = 'Identifiants incorrects. Vérifiez votre email/téléphone et votre mot de passe.';

    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = $request->validated('identifier');
        $password = $request->validated('password');

        $field = str_contains($identifier, '@') ? 'email' : 'phone';
        $user = User::query()->where($field, $identifier)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json([
                'error' => 'UNAUTHORIZED',
                'message' => self::GENERIC_LOGIN_ERROR,
                'details' => new \stdClass(),
            ], 401);
        }

        if ($user->status === UserStatus::Pending) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Compte en attente d\'activation',
                'details' => new \stdClass(),
            ], 403);
        }

        if ($user->status === UserStatus::Suspended) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Compte suspendu, contactez le support',
                'details' => new \stdClass(),
            ], 403);
        }

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ]);
    }
}
