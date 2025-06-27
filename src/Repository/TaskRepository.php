<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function createTask(string $title, string $description, string $status, User $user): Task
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setStatus($status);
        $task->setUser($user);
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setUpdatedAt(new \DateTimeImmutable());
        return $task;
    }
}
