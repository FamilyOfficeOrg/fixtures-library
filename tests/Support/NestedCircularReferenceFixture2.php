<?php

declare(strict_types=1);

namespace FamilyOffice\FixturesLibrary\Tests\Support;

use FamilyOffice\FixturesLibrary\FixtureInterface;

class NestedCircularReferenceFixture2 implements FixtureInterface
{
    public function getDependencies(): array
    {
        return [NestedCircularReferenceFixture3::class];
    }

    public function load(): void
    {
    }
}
