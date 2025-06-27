<?php

namespace App\Controller\Api\V1;

use App\Service\Api\V1\Task\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TaskApiController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    #[Route('/api/v1/tasks', name: 'app_api_v1_tasks_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): JsonResponse
    {
        return $this->taskService->handleGetTasks($this->getUser());
    }

    #[Route('/api/v1/tasks', name: 'app_api_v1_tasks_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createTask(Request $request, ValidatorInterface $validator): JsonResponse
    {
        return $this->taskService->handleCreateTask($request, $validator, $this->getUser());
    }

    #[Route('/api/v1/tasks/{id}', name: 'app_api_v1_tasks_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(int $id): JsonResponse
    {
        return $this->taskService->handleShowTask($id, $this->getUser());
    }

    #[Route('/api/v1/tasks/{id}', name: 'app_api_v1_tasks_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        return $this->taskService->handleUpdateTask($id, $request, $validator, $this->getUser());
    }

    #[Route('/api/v1/tasks/{id}', name: 'app_api_v1_tasks_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id): JsonResponse
    {
        return $this->taskService->handleDeleteTask($id, $this->getUser());
    }
}
