<?php

declare(strict_types=1);

namespace FamilyOffice\FixturesLibrary\Tests\Unit\Factory;

use FamilyOffice\FixturesLibrary\Exception\InvalidFixtureException;
use FamilyOffice\FixturesLibrary\Factory\DefaultFixtureFactory;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture1;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture2;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture3;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture4;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture5;
use FamilyOffice\FixturesLibrary\Tests\Support\InvalidFixture;
use PHPUnit\Framework\TestCase;

final class DefaultFixtureFactoryTest extends TestCase
{
    /**
     * @dataProvider dataProviderTestCreateInstance
     */
    public function testCreateInstance(string $fixtureClass): void
    {
        $fixtureFactory = new DefaultFixtureFactory();

        self::assertSame($fixtureClass, $fixtureFactory->createInstance($fixtureClass)::class);
    }

    public function dataProviderTestCreateInstance(): iterable
    {
        yield [Fixture1::class];
        yield [Fixture2::class];
        yield [Fixture3::class];
        yield [Fixture4::class];
        yield [Fixture5::class];
    }

    public function testCreateInstanceInvalidFixture(): void
    {
        $fixtureFactory = new DefaultFixtureFactory();

        $this->expectException(InvalidFixtureException::class);

        $fixtureFactory->createInstance(InvalidFixture::class);
    }
}
