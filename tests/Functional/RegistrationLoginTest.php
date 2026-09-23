<?php

namespace App\Tests\Functional;

use App\Tests\ApiTestCase;

class RegistrationLoginTest extends ApiTestCase
{
    public function testUserCanRegisterAndLogIn(): void
    {
        $this->jsonRequest('POST', '/api/users', [
            'email' => 'jane@example.com',
            'plainPassword' => 'S3curePass!',
        ]);

        $this->assertResponseStatusCodeSame(201);
        $created = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertSame('jane@example.com', $created['email']);
        $this->assertArrayNotHasKey('password', $created);
        $this->assertArrayNotHasKey('plainPassword', $created);

        $this->jsonRequest('POST', '/api/login', [
            'email' => 'jane@example.com',
            'password' => 'S3curePass!',
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);

        $this->client->request('GET', '/api/me', server: $this->authHeaders($data['token']));
        $this->assertResponseIsSuccessful();
        $me = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertSame('jane@example.com', $me['email']);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $this->jsonRequest('POST', '/api/users', [
            'email' => 'jane@example.com',
            'plainPassword' => 'S3curePass!',
        ]);

        $this->jsonRequest('POST', '/api/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeRequiresAuthentication(): void
    {
        $this->client->request('GET', '/api/me');

        $this->assertResponseStatusCodeSame(401);
    }
}
