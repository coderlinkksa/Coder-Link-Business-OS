<?php

namespace App\Modules\Identity\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\API\Requests\LoginRequest;
use App\Modules\Identity\Application\Actions\LoginAction;
use App\Modules\Identity\Application\Actions\LogoutAction;
use App\Modules\Identity\Domain\Exceptions\AuthenticationFailedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginAction $login,
        private readonly LogoutAction $logout,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $this->login->execute($request->toDTO());
        } catch (AuthenticationFailedException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Authenticated.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->logout->execute();

        return response()->json(['message' => 'Logged out.']);
    }
}
