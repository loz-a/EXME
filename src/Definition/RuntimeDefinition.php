<?php

declare(strict_types=1);

namespace EXME\Definition;

final class RuntimeDefinition
{
    /** @var array<class-string, ClassDefinition> */
    private array $definitions = [];

    /**
     * @param class-string $class
     */
    public function getClassDefinition(string $class): ClassDefinition
    {
        if (!isset($this->definitions[$class])) {
            if (!class_exists($class)) {
                throw new \RuntimeException(
                    sprintf(
                        'Class "%s" does not exist',
                        $class,
                    ),
                );
            }

            $definition = new ClassDefinition($class);

            if (!$definition->isInstantiable()) {
                throw new \RuntimeException(
                    sprintf(
                        'Class "%s" is not instantiable',
                        $class,
                    ),
                );
            }

            $this->definitions[$class] = $definition;
        }

        return $this->definitions[$class];
    }
}