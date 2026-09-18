<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Contract;

use EXME\Template\Lexer\Contract\TokenStreamInterface;
use EXME\Template\Parser\Node\Contract\NodeInterface;

interface ParserInterface 
{
    public function parse(TokenStreamInterface $tokens): NodeInterface;
}

