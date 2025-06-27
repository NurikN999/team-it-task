<?php

declare(strict_types=1);

namespace App\Service\Api\V1\Task;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

final class TaskService
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $em
    ) {}

    public function handleGetTasks(User $user, Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
        $status = $request->query->get('status');
        $sortBy = $request->query->get('sort_by', 'createdAt');
        $sortOrder = strtoupper($request->query->get('sort_order', 'DESC'));

        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }

        $allowedSortFields = ['createdAt', 'updatedAt', 'title', 'status'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'createdAt';
        }

        if ($status !== null && !in_array($status, ['pending', 'in_progress', 'completed'])) {
            $status = null;
        }

        $result = $this->taskRepository->findPaginatedTasksByUser(
            $user,
            $page,
            $limit,
            $status,
            $sortBy,
            $sortOrder
        );

        $data = [];
        foreach ($result['tasks'] as $task) {
            $data[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $statusCounts = $this->taskRepository->getTasksCountByStatus($user);

        return new JsonResponse([
            'message' => 'Tasks fetched successfully',
            'data' => $data,
            'pagination' => [
                'current_page' => $result['page'],
                'per_page' => $result['limit'],
                'total' => $result['total'],
                'total_pages' => $result['totalPages'],
                'has_next_page' => $result['hasNextPage'],
                'has_previous_page' => $result['hasPreviousPage'],
            ],
            'filters' => [
                'status' => $status,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
            'status_counts' => $statusCounts,
        ], Response::HTTP_OK);
    }

    public function handleCreateTask(Request $request, ValidatorInterface $validator, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
            return new JsonResponse(['message' => 'Invalid JSON data'], 400);
        }
        
        $constraints = new Assert\Collection([
            'title' => [
                new Assert\NotBlank(),
            ],
            'description' => [
                new Assert\Optional(),
            ],
            'status' => [
                new Assert\NotBlank(),
                new Assert\Choice(['pending', 'in_progress', 'completed']),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if ($violations->count() > 0) {
            return new JsonResponse(['message' => 'Invalid data', 'errors' => $violations], 400);
        }

        $task = $this->taskRepository->createTask(
            $data['title'],
            $data['description'],
            $data['status'],
            $user
        );

        $this->em->persist($task);
        $this->em->flush();

        return new JsonResponse(
            [
                'message' => 'Task created successfully',
                'data' => [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'status' => $task->getStatus(),
                    'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ], 201);
    }

    public function handleShowTask(int $id, User $user): JsonResponse
    {
        $task = $this->taskRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        return new JsonResponse([
            'message' => 'Task fetched successfully',
            'data' => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
        ], Response::HTTP_OK);
    }

    public function handleUpdateTask(int $id, Request $request, ValidatorInterface $validator, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
            return new JsonResponse(['message' => 'Invalid JSON data'], 400);
        }

        $task = $this->taskRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }
        
        $constraints = new Assert\Collection([
            'title' => [
                new Assert\Optional(),
            ],
            'description' => [
                new Assert\Optional(),
            ],
            'status' => [
                new Assert\Optional(),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if ($violations->count() > 0) {
            return new JsonResponse(['message' => 'Invalid data', 'errors' => $violations], 400);
        }

        $task->setTitle($data['title'] ?? $task->getTitle());
        $task->setDescription($data['description'] ?? $task->getDescription());
        $task->setStatus($data['status'] ?? $task->getStatus());

        $this->em->flush();

        return new JsonResponse([
            'message' => 'Task updated successfully',
            'data' => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
        ], Response::HTTP_OK);
    }

    public function handleDeleteTask(int $id, User $user): JsonResponse
    {
        $task = $this->taskRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        $this->em->remove($task);
        $this->em->flush();

        return new JsonResponse(
            [
                'message' => 'Task deleted successfully',
            ],
            Response::HTTP_NO_CONTENT
        );
    }
}