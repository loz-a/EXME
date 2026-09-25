<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Attribute\Contract;

use Stringable;

interface AttributeInterface extends Stringable
{
    public string $name { get; }
    
    public AttributeValueInterface $value { get; }
}