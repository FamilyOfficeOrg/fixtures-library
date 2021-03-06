<?php

declare(strict_types=1);

namespace FamilyOffice\FixturesLibrary\Tests\Unit\Loader;

use FamilyOffice\FixturesLibrary\Factory\DefaultFixtureFactory;
use FamilyOffice\FixturesLibrary\FixtureFactoryInterface;
use FamilyOffice\FixturesLibrary\Loader\ChainLoader;
use FamilyOffice\FixturesLibrary\Loader\DefaultFixtureLoader;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture1;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture2;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture3;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture4;
use FamilyOffice\FixturesLibrary\Tests\Support\Fixture5;
use PHPUnit\Framework\TestCase;

final class ChainLoaderTest extends TestCase
{
    public function testLoadDependencyChain(): void
    {
        $factory = $this->getMockBuilder(FixtureFactoryInterface::class)->onlyMethods(['createInstance'])->getMock();

        $instance = $this->getMockBuilder(Fixture1::class)->onlyMethods(['load'])->getMock();
        $instance->expects(self::exactly(5))->method('load');

        $factory->expects(self::exactly(5))->method('createInstance')->willReturn($instance);

        $loader = new ChainLoader($factory, new DefaultFixtureLoader());
        $loader->loadDependencyChain([
            Fixture5::class => [
                Fixture4::class => [],
                Fixture3::class => [
                    Fixture1::class => [
                        Fixture2::class => [],
                    ],
                ],
            ],
        ]);
    }

    public function testCreateDefault(): void
    {
        $expected = new ChainLoader(new DefaultFixtureFactory(), new DefaultFixtureLoader());
        $actual = ChainLoader::createDefault();

        self::assertEquals($expected, $actual);
    }
}
