<?php

namespace App\Tests\Controller\Api\V1;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskApiControllerTest extends WebTestCase
{
    private $client;
    private $userRepository;
    private $taskRepository;
    private $jwtManager;
    private $user;
    private $token;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);
        $this->jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);

        // Create a test user
        $this->user = new User();
        $this->user->setEmail('test@example.com');
        $this->user->setPassword('password123');
        $this->user->setFirstName('Test');
        $this->user->setLastName('User');

        $this->userRepository->save($this->user, true);

        // Generate JWT token for the user
        $this->token = $this->jwtManager->create($this->user);
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->taskRepository->createQueryBuilder('t')
            ->delete()
            ->getQuery()
            ->execute();

        $this->userRepository->createQueryBuilder('u')
            ->delete()
            ->getQuery()
            ->execute();

        parent::tearDown();
    }

    public function testGetTasksWithoutAuthentication(): void
    {
        $this->client->request('GET', '/api/v1/tasks');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetTasksWithAuthentication(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task, true);

        $this->client->request('GET', '/api/v1/tasks', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Tasks fetched successfully', $responseData['message']);
        $this->assertIsArray($responseData['data']);
    }

    public function testCreateTaskWithoutAuthentication(): void
    {
        $this->client->request('POST', '/api/v1/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'New Task',
            'description' => 'New Description',
            'status' => 'pending'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateTaskWithValidData(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'New Description',
            'status' => 'pending'
        ];

        $this->client->request('POST', '/api/v1/tasks', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($taskData));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Task created successfully', $responseData['message']);
    }

    public function testCreateTaskWithInvalidData(): void
    {
        $taskData = [
            'title' => '',
            'description' => 'New Description',
            'status' => 'invalid_status'
        ];

        $this->client->request('POST', '/api/v1/tasks', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($taskData));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testShowTaskWithoutAuthentication(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task, true);

        $this->client->request('GET', '/api/v1/tasks/' . $task->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testShowTaskWithAuthentication(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task, true);

        $this->client->request('GET', '/api/v1/tasks/' . $task->getId(), [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Task fetched successfully', $responseData['message']);
        $this->assertEquals($task->getId(), $responseData['data']['id']);
    }

    public function testShowNonExistentTask(): void
    {
        $this->client->request('GET', '/api/v1/tasks/999', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateTaskWithoutAuthentication(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task, true);

        $this->client->request('PUT', '/api/v1/tasks/' . $task->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Updated Task',
            'description' => 'Updated Description',
            'status' => 'completed'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdateTaskWithValidData(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task, true);

        $updateData = [
            'title' => 'Updated Task',
            'description' => 'Updated Description',
            'status' => 'completed'
        ];

        $this->client->request('PUT', '/api/v1/tasks/' . $task->getId(), [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($updateData));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Task updated successfully', $responseData['message']);
        $this->assertEquals('Updated Task', $responseData['data']['title']);
    }

    public function testDeleteTaskWithoutAuthentication(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task, true);

        $this->client->request('DELETE', '/api/v1/tasks/' . $task->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testDeleteTaskWithAuthentication(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus('pending');
        $task->setUser($this->user);
        $task->setCreatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task, true);

        $this->client->request('DELETE', '/api/v1/tasks/' . $task->getId(), [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Task deleted successfully', $responseData['message']);

        $this->assertNull($this->taskRepository->find($task->getId()));
    }

    public function testDeleteNonExistentTask(): void
    {
        $this->client->request('DELETE', '/api/v1/tasks/999', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUserCannotAccessOtherUserTask(): void
    {
        $otherUser = new User();
        $otherUser->setEmail('other@example.com');
        $otherUser->setPassword('password123');
        $otherUser->setFirstName('Other');
        $otherUser->setLastName('User');

        $this->userRepository->save($otherUser, true);

        $task = new Task();
        $task->setTitle('Other User Task');
        $task->setDescription('Other User Description');
        $task->setStatus('pending');
        $task->setUser($otherUser);
        $task->setCreatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task, true);

        $this->client->request('GET', '/api/v1/tasks/' . $task->getId(), [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
