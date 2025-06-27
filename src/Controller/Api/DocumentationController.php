<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Task Management API',
    version: '1.0.0',
    description: 'API for managing user tasks with JWT authentication',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'support@example.com'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Development server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'JWT token for authentication. Include "Bearer " prefix.'
)]
class DocumentationController extends AbstractController
{
    #[Route('/api/doc', name: 'app_api_doc', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('api/documentation.html.twig');
    }
} 