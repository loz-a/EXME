<?php

declare(strict_types=1);

namespace EXME\Definition;

use ReflectionParameter;
use ReflectionType;

final readonly class ParameterDefinition
{
    public function __construct(
        public string $name,
        public ?ReflectionType $type,
        public bool $isOptional,
        public bool $hasDefaultValue,
        public mixed $defaultValue,
    ) {
    }

    public static function fromReflection(
        ReflectionParameter $parameter,
    ): self {
        return new self(
            name: $parameter->getName(),
            type: $parameter->getType(),
            isOptional: $parameter->isOptional(),
            hasDefaultValue: $parameter->isDefaultValueAvailable(),
            defaultValue: $parameter->isDefaultValueAvailable()
                ? $parameter->getDefaultValue()
                : null,
        );
    }
}