<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Attribute\Value;

use EXME\Template\Parser\Node\Attribute\Contract\AttributeValueInterface;

final class Boolean implements AttributeValueInterface
{
    public function __construct(
        public readonly bool $value,
    ) {}

    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }
}