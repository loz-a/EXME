<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Contract;

use EXME\Template\Lexer\Contract\TokenCollectionInterface;
use EXME\Template\Parser\Node\Contract\NodeInterface;

interface ParserInterface 
{
    public function parse(TokenCollectionInterface $tokens): NodeInterface;
}

