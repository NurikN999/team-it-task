<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class UserApiControllerTest extends WebTestCase
{
    private $token;
    private $user;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->createUser();
        $this->token = static::getContainer()->get(JWTTokenManagerInterface::class)->create($this->user);
    }

    private function createUser(): void
    {
        $this->user = new User();
        $this->user->setEmail('test@test.com');
        $this->user->setPassword('password');
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/v1/users');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testIndexWithAuthentication(): void
    {
        $this->client->request('GET', '/api/v1/users', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
