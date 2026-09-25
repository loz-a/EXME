<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Attribute\Value;

use EXME\Template\Parser\Node\Attribute\Contract\AttributeValueInterface;

final class Composite implements AttributeValueInterface
{
    public readonly array $attributeValues; 

    public function __construct(
        AttributeValueInterface ...$attributeValues,
    ){
        $this->attributeValues = $attributeValues;
    }

    public function __toString(): string
    {
        return implode(
            ' . ',
            array_map(
                static fn (AttributeValueInterface $attrValue): string => (string) $attrValue,
                $this->attributeValues,
            ),
        );
    }
}