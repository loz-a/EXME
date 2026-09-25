<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Attribute\Contract;

use EXME\Template\Lexer\Token;

interface AttributeFactoryInterface
{
    public function create(string $name, Token ...$tokenValues): AttributeInterface;
}