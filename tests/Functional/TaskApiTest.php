<?php

namespace App\Tests\Functional;

use App\Tests\ApiTestCase;

class TaskApiTest extends ApiTestCase
{
    public function testAuthenticatedUserCanCreateAndListOwnTasks(): void
    {
        $token = $this->registerAndLogin('owner@example.com');

        $this->jsonRequest('POST', '/api/tasks', [
            'title' => 'Write portfolio README',
            'priority' => 'high',
        ], $this->authHeaders($token));

        $this->assertResponseStatusCodeSame(201);
        $task = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertSame('Write portfolio README', $task['title']);
        $this->assertFalse($task['isDone']);

        $this->client->request('GET', '/api/tasks', server: $this->authHeaders($token));
        $this->assertResponseIsSuccessful();
        $collection = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $collection['member']);
    }

    public function testUsersCannotSeeEachOthersTasks(): void
    {
        $ownerToken = $this->registerAndLogin('owner2@example.com');
        $this->jsonRequest('POST', '/api/tasks', ['title' => 'Private task'], $this->authHeaders($ownerToken));
        $created = json_decode((string) $this->client->getResponse()->getContent(), true);

        $intruderToken = $this->registerAndLogin('intruder@example.com');

        $this->client->request('GET', '/api/tasks', server: $this->authHeaders($intruderToken));
        $collection = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $collection['member']);

        $this->client->request('GET', $created['@id'], server: $this->authHeaders($intruderToken));
        $this->assertResponseStatusCodeSame(403);
    }

    public function testTasksRequireAuthentication(): void
    {
        $this->client->request('GET', '/api/tasks');

        $this->assertResponseStatusCodeSame(401);
    }
}
