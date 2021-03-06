<?php

declare(strict_types=1);

namespace FamilyOffice\FixturesLibrary\Dependency;

use FamilyOffice\FixturesLibrary\Computer\DefaultFixtureComputer;
use FamilyOffice\FixturesLibrary\Computer\OnFlyFixtureComputer;
use FamilyOffice\FixturesLibrary\Exception\CircularReferenceException;
use FamilyOffice\FixturesLibrary\Exception\InvalidFixtureException;
use FamilyOffice\FixturesLibrary\Factory\DefaultFixtureFactory;
use FamilyOffice\FixturesLibrary\FixtureComputerInterface;
use FamilyOffice\FixturesLibrary\FixtureFactoryInterface;
use FamilyOffice\FixturesLibrary\FixtureInterface;
use Safe\Exceptions\SplException;
use Safe\Exceptions\StringsException;

final class ChainBuilder
{
    private Validator $validator;

    /**
     * @var string[]
     */
    private array $computed = [];

    public function __construct(
        private FixtureFactoryInterface $fixtureFactory,
        private FixtureComputerInterface $fixtureComputer
    ) {
        $this->validator = new Validator();
    }

    public static function createDefault(): self
    {
        return new self(new DefaultFixtureFactory(), new DefaultFixtureComputer());
    }

    public static function createQuickLoader(): self
    {
        return new self(new DefaultFixtureFactory(), new OnFlyFixtureComputer());
    }

    /**
     * @param FixtureInterface[] $fixtures
     *
     * @throws CircularReferenceException
     * @throws InvalidFixtureException
     * @throws SplException
     * @throws StringsException
     */
    public function build(array $fixtures): array
    {
        $dependencyChain = [];

        // way faster than waiting for computation
        if ([] === $fixtures) {
            return [];
        }

        foreach ($fixtures as $fixture) {
            $fixtureClass = $fixture::class;

            if ($this->alreadyComputed($fixtureClass)) {
                continue;
            }

            $this->validator->validateDependencyClass($fixtureClass);
            $dependencyChain[$fixtureClass] = $this->buildDependencySubChain(
                $fixture->getDependencies(),
                [$fixtureClass]
            );

            $this->computeFixture($fixture, $fixtureClass);
        }

        return $dependencyChain;
    }

    private function computeFixture(FixtureInterface $fixture, string $fixtureClass): void
    {
        $this->fixtureComputer->computeFixture($fixture);
        $this->computed[] = $fixtureClass;
    }

    /**
     * @throws CircularReferenceException
     * @throws InvalidFixtureException
     * @throws SplException
     * @throws StringsException
     */
    private function buildDependencySubChain(array $dependencyClasses, array $scopedChain): array
    {
        $dependencySubChain = [];

        foreach ($dependencyClasses as $dependencyClass) {
            if ($this->alreadyComputed($dependencyClass)) {
                continue;
            }

            if (\in_array($dependencyClass, $scopedChain, true)) {
                throw new CircularReferenceException('Circular reference detected!');
            }

            $this->validator->validateDependencyClass($dependencyClass);
            $scopedChain[] = $dependencyClass;
            $dependency = $this->fixtureFactory->createInstance($dependencyClass);
            $dependencySubChain[$dependencyClass] = $this->buildDependencySubChain(
                $dependency->getDependencies(),
                $scopedChain
            );

            $this->computeFixture($dependency, $dependencyClass);
        }

        return $dependencySubChain;
    }

    private function alreadyComputed(string $fixture): bool
    {
        return \in_array($fixture, $this->computed, true);
    }
}
