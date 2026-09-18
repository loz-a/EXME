<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Contract;

use EXME\Template\Lexer\Contract\TokenStreamInterface;

interface NodeFactoryInterface
{
    public function create(TokenStreamInterface $tokens): NodeInterface;
}