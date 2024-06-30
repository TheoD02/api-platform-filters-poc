<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Tests\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use function Zenstruck\Foundry\Persistence\flush_after;

class AppFixtures extends Fixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        ini_set('memory_limit', '2G');
        flush_after(static function (): void {
            UserFactory::createMany(10000);
        });

        $manager->flush();
    }
}
