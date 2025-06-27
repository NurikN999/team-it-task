<?php

namespace App\Controller\Api\V1;

use App\Service\Api\V1\Auth\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Authentication', description: 'User authentication endpoints')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService
    )
    {
        
    }

    #[Route('/api/v1/auth/register', name: 'app_api_v1_auth_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/register',
        summary: 'Register a new user',
        description: 'Creates a new user account and returns JWT token',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        description: 'User registration data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'first_name', type: 'string', minLength: 3, example: 'John'),
                new OA\Property(property: 'last_name', type: 'string', minLength: 3, example: 'Doe'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'password123'),
            ],
            required: ['first_name', 'last_name', 'email', 'password']
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User registered successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'User registered successfully'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                                new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                            ]
                        ),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - validation errors or user already exists',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'User already exists'),
                new OA\Property(
                    property: 'first_name',
                    type: 'string',
                    example: 'This value should not be blank.'
                ),
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    example: 'This value is not a valid email address.'
                ),
            ]
        )
    )]
    public function register(Request $request, ValidatorInterface $validator): Response
    {
        return $this->authService->handleRegister($request, $validator);
    }

    #[Route('/api/v1/auth/login', name: 'app_api_v1_auth_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'User login',
        description: 'Authenticates user and returns JWT token',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        description: 'User login credentials',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password123'),
            ],
            required: ['email', 'password']
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Login successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                                new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                            ]
                        ),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - validation errors',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    example: 'This value is not a valid email address.'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    example: 'This value should not be blank.'
                ),
            ]
        )
    )]
    public function login(Request $request, ValidatorInterface $validator): Response
    {
        return $this->authService->handleLogin($request, $validator);
    }
}
