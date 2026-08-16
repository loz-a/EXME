<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

final readonly class ComponentNode
{
    /**
     * @param array<string, string> $attributes
     */
    public function __construct(
        public string $name,
        public array $attributes,
    ) {
    }
}