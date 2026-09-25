<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Attribute\Value;

use EXME\Template\Parser\Node\Attribute\Contract\AttributeValueInterface;
use InvalidArgumentException;

final class Numeric implements AttributeValueInterface
{
    public function __construct(
        public readonly string $numeric,
    ){
        if (!is_numeric($numeric)) {
            throw new InvalidArgumentException('It is expected a number is to be passed');
        }
    }

    public function __toString(): string
    {
        return $this->numeric;
    }
}