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

    /**
     * Get paginated tasks for a user
     */
    public function findPaginatedTasksByUser(User $user, int $page = 1, int $limit = 10, ?string $status = null, ?string $sortBy = 'createdAt', string $sortOrder = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->setParameter('user', $user);

        // Add status filter if provided
        if ($status !== null) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $status);
        }

        $qb->orderBy('t.' . $sortBy, $sortOrder);

        $countQb = clone $qb;
        $countQb->resetDQLPart('orderBy');
        $totalCount = $countQb->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        $tasks = $qb->getQuery()->getResult();

        return [
            'tasks' => $tasks,
            'total' => $totalCount,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($totalCount / $limit),
            'hasNextPage' => $page < ceil($totalCount / $limit),
            'hasPreviousPage' => $page > 1,
        ];
    }

    /**
     * Get tasks count by status for a user
     */
    public function getTasksCountByStatus(User $user): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t.status, COUNT(t.id) as count')
            ->where('t.user = :user')
            ->setParameter('user', $user)
            ->groupBy('t.status');

        $result = $qb->getQuery()->getResult();

        $counts = [
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'total' => 0,
        ];

        foreach ($result as $row) {
            $counts[$row['status']] = $row['count'];
            $counts['total'] += $row['count'];
        }

        return $counts;
    }
}
