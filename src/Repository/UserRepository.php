<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function createUser(string $email, string $plainPassword, UserPasswordHasherInterface $hasher, string $first_name, string $last_name): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword(
            $hasher->hashPassword($user, $plainPassword)
        );
        $user->setFirstName($first_name);
        $user->setLastName($last_name);
        return $user;
    }
}
