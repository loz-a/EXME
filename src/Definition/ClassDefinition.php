<?php

declare(strict_types=1);

namespace EXME\Definition;

use ReflectionClass;

final class ClassDefinition
{
    /** @var array<string, ParameterDefinition>|null */
    private ?array $parameters = null;

    private readonly ReflectionClass $reflection;

    /**
     * @param class-string $class
     */
    public function __construct(string $class)
    {
        $this->reflection = new ReflectionClass($class);
    }

    public function getReflection(): ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return list<class-string>
     */
    public function getInterfaces(): array
    {
        return $this->reflection->getInterfaceNames();
    }

    /**
     * @return array<string, ParameterDefinition>
     */
    public function getParameters(): array
    {
        if ($this->parameters === null) {
            $this->parameters = [];

            $constructor = $this->reflection->getConstructor();

            if ($constructor !== null) {
                foreach ($constructor->getParameters() as $parameter) {
                    $definition = ParameterDefinition::fromReflection(
                        $parameter,
                    );

                    $this->parameters[$definition->name] = $definition;
                }
            }
        }

        return $this->parameters;
    }

    public function implements(string $interface): bool
    {
        return $this->reflection->implementsInterface($interface);
    }

    public function isInstantiable(): bool
    {
        return $this->reflection->isInstantiable();
    }
}