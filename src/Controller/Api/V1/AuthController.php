<?php

namespace App\Controller\Api\V1;

use App\Service\Api\V1\Auth\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService
    )
    {
        
    }

    #[Route('/api/v1/auth/register', name: 'app_api_v1_auth_register', methods: ['POST'])]
    public function register(Request $request, ValidatorInterface $validator): Response
    {
        return $this->authService->handleRegister($request, $validator);
    }

    #[Route('/api/v1/auth/login', name: 'app_api_v1_auth_login', methods: ['POST'])]
    public function login(Request $request, ValidatorInterface $validator): Response
    {
        return $this->authService->handleLogin($request, $validator);
    }
}
