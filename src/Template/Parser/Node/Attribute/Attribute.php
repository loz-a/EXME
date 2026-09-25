<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Attribute;

use EXME\Template\Parser\Node\Attribute\Contract\AttributeInterface;
use EXME\Template\Parser\Node\Attribute\Contract\AttributeValueInterface;

final class Attribute implements AttributeInterface
{
    public function __construct(
        public readonly string $name,
        public readonly AttributeValueInterface $value,
    ) {}

    public function __toString(): string
    {
        return sprintf(
            '%s => %s',
            var_export($this->name, true),
            $this->value,
        );
    }
}