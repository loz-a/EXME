<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Contract;

use EXME\Template\Lexer\Contract\TokenCollectionInterface;

interface NodeFactoryInterface
{
    public function create(TokenCollectionInterface $tokens): NodeInterface;
}