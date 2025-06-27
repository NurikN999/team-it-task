<?php

namespace App\Controller\Api\V1;

use App\Service\Api\V1\Task\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tasks', description: 'Task management endpoints')]
final class TaskApiController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    #[Route('/api/v1/tasks', name: 'app_api_v1_tasks_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        path: '/api/v1/tasks',
        summary: 'Get paginated tasks',
        description: 'Retrieve paginated list of tasks for the authenticated user',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, default: 1, example: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Number of items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 10, example: 10)
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Filter by task status',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'completed'], example: 'pending')
    )]
    #[OA\Parameter(
        name: 'sort_by',
        description: 'Sort field',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['createdAt', 'updatedAt', 'title', 'status'], default: 'createdAt', example: 'createdAt')
    )]
    #[OA\Parameter(
        name: 'sort_order',
        description: 'Sort order',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC'], default: 'DESC', example: 'DESC')
    )]
    #[OA\Response(
        response: 200,
        description: 'Tasks retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Tasks fetched successfully'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'title', type: 'string', example: 'Complete project documentation'),
                            new OA\Property(property: 'description', type: 'string', example: 'Write comprehensive documentation for the API'),
                            new OA\Property(property: 'status', type: 'string', example: 'pending'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01 10:00:00'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01 10:00:00'),
                        ]
                    )
                ),
                new OA\Property(
                    property: 'pagination',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'per_page', type: 'integer', example: 10),
                        new OA\Property(property: 'total', type: 'integer', example: 25),
                        new OA\Property(property: 'total_pages', type: 'integer', example: 3),
                        new OA\Property(property: 'has_next_page', type: 'boolean', example: true),
                        new OA\Property(property: 'has_previous_page', type: 'boolean', example: false),
                    ]
                ),
                new OA\Property(
                    property: 'filters',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'status', type: 'string', nullable: true, example: 'pending'),
                        new OA\Property(property: 'sort_by', type: 'string', example: 'createdAt'),
                        new OA\Property(property: 'sort_order', type: 'string', example: 'DESC'),
                    ]
                ),
                new OA\Property(
                    property: 'status_counts',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'pending', type: 'integer', example: 10),
                        new OA\Property(property: 'in_progress', type: 'integer', example: 5),
                        new OA\Property(property: 'completed', type: 'integer', example: 10),
                        new OA\Property(property: 'total', type: 'integer', example: 25),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - JWT token required',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
            ]
        )
    )]
    public function index(Request $request): JsonResponse
    {
        return $this->taskService->handleGetTasks($this->getUser(), $request);
    }

    #[Route('/api/v1/tasks', name: 'app_api_v1_tasks_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        path: '/api/v1/tasks',
        summary: 'Create a new task',
        description: 'Create a new task for the authenticated user',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        description: 'Task data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Complete project documentation'),
                new OA\Property(property: 'description', type: 'string', example: 'Write comprehensive documentation for the API'),
                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed'], example: 'pending'),
            ],
            required: ['title', 'status']
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Task created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Task created successfully'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Complete project documentation'),
                        new OA\Property(property: 'description', type: 'string', example: 'Write comprehensive documentation for the API'),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01 10:00:00'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01 10:00:00'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - validation errors',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Invalid data'),
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'This value should not be blank.'
                ),
                new OA\Property(
                    property: 'status',
                    type: 'string',
                    example: 'The value you selected is not a valid choice.'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - JWT token required',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
            ]
        )
    )]
    public function createTask(Request $request, ValidatorInterface $validator): JsonResponse
    {
        return $this->taskService->handleCreateTask($request, $validator, $this->getUser());
    }

    #[Route('/api/v1/tasks/{id}', name: 'app_api_v1_tasks_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        path: '/api/v1/tasks/{id}',
        summary: 'Get a specific task',
        description: 'Retrieve a specific task by ID for the authenticated user',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Task ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Task retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Task fetched successfully'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Complete project documentation'),
                        new OA\Property(property: 'description', type: 'string', example: 'Write comprehensive documentation for the API'),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01 10:00:00'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01 10:00:00'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Task not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Task not found'),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - JWT token required',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
            ]
        )
    )]
    public function show(int $id): JsonResponse
    {
        return $this->taskService->handleShowTask($id, $this->getUser());
    }

    #[Route('/api/v1/tasks/{id}', name: 'app_api_v1_tasks_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Put(
        path: '/api/v1/tasks/{id}',
        summary: 'Update a task',
        description: 'Update an existing task for the authenticated user',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Task ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\RequestBody(
        description: 'Task update data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string', example: 'Updated task title'),
                new OA\Property(property: 'description', type: 'string', example: 'Updated task description'),
                new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed'], example: 'in_progress'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Task updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Task updated successfully'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Updated task title'),
                        new OA\Property(property: 'description', type: 'string', example: 'Updated task description'),
                        new OA\Property(property: 'status', type: 'string', example: 'in_progress'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01 10:00:00'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01 11:00:00'),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Task not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Task not found'),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request - validation errors',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Invalid data'),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - JWT token required',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
            ]
        )
    )]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        return $this->taskService->handleUpdateTask($id, $request, $validator, $this->getUser());
    }

    #[Route('/api/v1/tasks/{id}', name: 'app_api_v1_tasks_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(
        path: '/api/v1/tasks/{id}',
        summary: 'Delete a task',
        description: 'Delete a task for the authenticated user',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Task ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Response(
        response: 204,
        description: 'Task deleted successfully'
    )]
    #[OA\Response(
        response: 404,
        description: 'Task not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Task not found'),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - JWT token required',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
            ]
        )
    )]
    public function delete(int $id): JsonResponse
    {
        return $this->taskService->handleDeleteTask($id, $this->getUser());
    }
}
