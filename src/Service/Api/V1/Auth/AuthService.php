<?php

declare(strict_types=1);

namespace App\Service\Api\V1\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly UserRepository $userRepository
    ) {}

    public function handleRegister(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
            return new JsonResponse(['message' => 'Invalid JSON data'], 400);
        }

        $constraints = new Assert\Collection([
            'first_name' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3]),
            ],
            'last_name' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3]),
            ],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 8]),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }
        
        $user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if ($user) {
            return new JsonResponse(['message' => 'User already exists'], 400);
        }

        $user = $this->userRepository->createUser(
            $data['email'],
            $data['password'],
            $this->passwordHasher,
            $data['first_name'],
            $data['last_name']
        );

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(
            [
                'message' => 'User registered successfully',
                'data' => [
                    'token' => $this->jwtManager->create($user),
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'first_name' => $user->getFirstName(),
                        'last_name' => $user->getLastName(),
                    ]
                ]
            ], 
            201
        );
    }

    public function handleLogin(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
            return new JsonResponse(['message' => 'Invalid JSON data'], 400);
        }

        $constraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password' => [
                new Assert\NotBlank(),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['message' => 'Invalid credentials'], 401);
        }

        return new JsonResponse(
            [
                'message' => 'Login successful',
                'data' => [
                    'token' => $this->jwtManager->create($user),
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'first_name' => $user->getFirstName(),
                        'last_name' => $user->getLastName(),
                    ]
                ]
            ], 
            200
        );
    }
}