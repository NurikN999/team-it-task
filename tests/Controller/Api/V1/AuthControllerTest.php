<?php

namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/auth');

        self::assertResponseIsSuccessful();
    }
}
