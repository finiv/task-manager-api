<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function jsonRequest(string $method, string $uri, array $data = [], array $headers = []): void
    {
        $this->client->jsonRequest($method, $uri, $data, array_merge([
            'CONTENT_TYPE' => 'application/ld+json',
            'HTTP_ACCEPT' => 'application/ld+json',
        ], $headers));
    }

    protected function registerAndLogin(string $email = 'jane@example.com', string $password = 'S3curePass!'): string
    {
        $this->jsonRequest('POST', '/api/users', [
            'email' => $email,
            'plainPassword' => $password,
        ]);

        $this->jsonRequest('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        return $data['token'];
    }

    protected function authHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer '.$token];
    }
}
