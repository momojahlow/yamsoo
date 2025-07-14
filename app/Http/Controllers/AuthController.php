<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    // Méthodes d'authentification supprimées - utilisation de Laravel Breeze
    // Les méthodes login, register, logout sont gérées par les contrôleurs Breeze

    public function checkAuth(): JsonResponse
    {
        $user = $this->authService->getCurrentUser();

        if ($user) {
            return response()->json([
                'user' => $user,
                'authenticated' => true
            ]);
        }

        return response()->json([
            'authenticated' => false
        ], 401);
    }
}
