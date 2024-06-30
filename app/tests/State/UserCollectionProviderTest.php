<?php

declare(strict_types=1);

namespace App\Tests\State;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\ApiResource\UserResource;
use App\Tests\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
final class UserCollectionProviderTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    public function testProviderWithoutFilter(): void
    {
        // Arrange
        UserFactory::createMany(3);
        $client = self::createClient();

        // Act
        $response = $client->request('GET', '/api/users');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(UserResource::class);
        $this->assertCount(3, $response->toArray()['hydra:member']);
    }

    public function testProviderWithNameContainsFilter(): void
    {
        // Arrange
        UserFactory::createMany(3);
        UserFactory::createOne([
            'name' => 'This is a name 1',
        ]);
        $client = self::createClient();

        // Act
        $response = $client->request('GET', '/api/users', [
            'query' => [
                'name[contains]' => 'name 1',
            ],
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(UserResource::class);
        $this->assertCount(1, $response->toArray()['hydra:member']);
    }

    public function testProviderWithCustomFilter(): void
    {
        // Arrange
        UserFactory::createMany(10);
        $client = self::createClient();

        // Act
        $response = $client->request('GET', '/api/users', [
            'query' => [
                'customFilter' => 5,
            ],
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(UserResource::class);
        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testProviderWithNameContainsAndCustomFilter(): void
    {
        // Arrange
        UserFactory::createMany(10);
        UserFactory::createOne([
            'name' => 'This is a name 1',
        ]);
        $client = self::createClient();

        // Act
        $response = $client->request('GET', '/api/users', [
            'query' => [
                'name[contains]' => 'name 1',
                'customFilter' => 5,
            ],
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(UserResource::class);
        $this->assertCount(1, $response->toArray()['hydra:member']);
    }

    public function testProviderWithNameContainsAndCustomFilterWithNoResults(): void
    {
        // Arrange
        UserFactory::createOne([
            'name' => 'This is a name 1',
        ]); // Because ID is 1 and we filter by 5 it should return 0
        UserFactory::createMany(10);
        $client = self::createClient();

        // Act
        $response = $client->request('GET', '/api/users', [
            'query' => [
                'name[contains]' => 'name 1',
                'customFilter' => 5,
            ],
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(UserResource::class);
        $this->assertCount(0, $response->toArray()['hydra:member']);
    }
}
