<?php

namespace App\Tests\Service\Api\V1\Task;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Service\Api\V1\Task\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskServiceTest extends TestCase
{
    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private User $user;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->taskService = new TaskService(
            $this->taskRepository,
            $this->entityManager
        );

        $this->user = new User();
        $this->user->setEmail('test@example.com');
        $this->user->setFirstName('Test');
        $this->user->setLastName('User');
    }

    public function testHandleGetTasksWithPagination(): void
    {
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task1->setDescription('Description 1');
        $task1->setStatus('pending');
        $task1->setUser($this->user);
        $task1->setCreatedAt(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $task1->setUpdatedAt(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $task2 = new Task();
        $task2->setTitle('Task 2');
        $task2->setDescription('Description 2');
        $task2->setStatus('completed');
        $task2->setUser($this->user);
        $task2->setCreatedAt(new \DateTimeImmutable('2024-01-02 10:00:00'));
        $task2->setUpdatedAt(new \DateTimeImmutable('2024-01-02 10:00:00'));

        $mockResult = [
            'tasks' => [$task1, $task2],
            'total' => 2,
            'page' => 1,
            'limit' => 10,
            'totalPages' => 1,
            'hasNextPage' => false,
            'hasPreviousPage' => false,
        ];

        $mockStatusCounts = [
            'pending' => 1,
            'in_progress' => 0,
            'completed' => 1,
            'total' => 2,
        ];

        $this->taskRepository->expects($this->once())
            ->method('findPaginatedTasksByUser')
            ->with($this->user, 1, 10, null, 'createdAt', 'DESC')
            ->willReturn($mockResult);

        $this->taskRepository->expects($this->once())
            ->method('getTasksCountByStatus')
            ->with($this->user)
            ->willReturn($mockStatusCounts);

        // Create request with pagination parameters
        $request = new Request([
            'page' => '1',
            'limit' => '10',
            'sort_by' => 'createdAt',
            'sort_order' => 'DESC'
        ]);

        $response = $this->taskService->handleGetTasks($this->user, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('Tasks fetched successfully', $responseData['message']);
        $this->assertCount(2, $responseData['data']);
        $this->assertEquals('Task 1', $responseData['data'][0]['title']);
        $this->assertEquals('Task 2', $responseData['data'][1]['title']);
        
        // Check pagination info
        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertEquals(1, $responseData['pagination']['current_page']);
        $this->assertEquals(10, $responseData['pagination']['per_page']);
        $this->assertEquals(2, $responseData['pagination']['total']);
        $this->assertEquals(1, $responseData['pagination']['total_pages']);
        $this->assertFalse($responseData['pagination']['has_next_page']);
        $this->assertFalse($responseData['pagination']['has_previous_page']);
        
        // Check filters
        $this->assertArrayHasKey('filters', $responseData);
        $this->assertNull($responseData['filters']['status']);
        $this->assertEquals('createdAt', $responseData['filters']['sort_by']);
        $this->assertEquals('DESC', $responseData['filters']['sort_order']);
        
        // Check status counts
        $this->assertArrayHasKey('status_counts', $responseData);
        $this->assertEquals(1, $responseData['status_counts']['pending']);
        $this->assertEquals(1, $responseData['status_counts']['completed']);
        $this->assertEquals(2, $responseData['status_counts']['total']);
    }

    public function testHandleGetTasksWithStatusFilter(): void
    {
        $task = new Task();
        $task->setTitle('Pending Task');
        $task->setDescription('Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $task->setUpdatedAt(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $mockResult = [
            'tasks' => [$task],
            'total' => 1,
            'page' => 1,
            'limit' => 10,
            'totalPages' => 1,
            'hasNextPage' => false,
            'hasPreviousPage' => false,
        ];

        $mockStatusCounts = [
            'pending' => 1,
            'in_progress' => 0,
            'completed' => 0,
            'total' => 1,
        ];

        $this->taskRepository->expects($this->once())
            ->method('findPaginatedTasksByUser')
            ->with($this->user, 1, 10, 'pending', 'createdAt', 'DESC')
            ->willReturn($mockResult);

        $this->taskRepository->expects($this->once())
            ->method('getTasksCountByStatus')
            ->with($this->user)
            ->willReturn($mockStatusCounts);

        $request = new Request([
            'status' => 'pending'
        ]);

        $response = $this->taskService->handleGetTasks($this->user, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertEquals('pending', $responseData['filters']['status']);
        $this->assertCount(1, $responseData['data']);
        $this->assertEquals('pending', $responseData['data'][0]['status']);
    }

    public function testHandleGetTasksWithInvalidParameters(): void
    {
        $task = new Task();
        $task->setTitle('Task');
        $task->setDescription('Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable('2024-01-01 10:00:00'));
        $task->setUpdatedAt(new \DateTimeImmutable('2024-01-01 10:00:00'));

        $mockResult = [
            'tasks' => [$task],
            'total' => 1,
            'page' => 1,
            'limit' => 100,
            'totalPages' => 1,
            'hasNextPage' => false,
            'hasPreviousPage' => false,
        ];

        $mockStatusCounts = [
            'pending' => 1,
            'in_progress' => 0,
            'completed' => 0,
            'total' => 1,
        ];

        $this->taskRepository->expects($this->once())
            ->method('findPaginatedTasksByUser')
            ->with($this->user, 1, 100, null, 'createdAt', 'DESC')
            ->willReturn($mockResult);

        $this->taskRepository->expects($this->once())
            ->method('getTasksCountByStatus')
            ->with($this->user)
            ->willReturn($mockStatusCounts);

        // Test with invalid parameters that should be sanitized
        $request = new Request([
            'page' => '0', // Should become 1
            'limit' => '200', // Should become 100 (max)
            'status' => 'invalid_status', // Should become null
            'sort_by' => 'invalid_field', // Should become 'createdAt'
            'sort_order' => 'invalid_order' // Should become 'DESC'
        ]);

        $response = $this->taskService->handleGetTasks($this->user, $request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        
        // Check that parameters were sanitized
        $this->assertEquals(1, $responseData['pagination']['current_page']);
        $this->assertEquals(100, $responseData['pagination']['per_page']);
        $this->assertNull($responseData['filters']['status']);
        $this->assertEquals('createdAt', $responseData['filters']['sort_by']);
        $this->assertEquals('DESC', $responseData['filters']['sort_order']);
    }
} 