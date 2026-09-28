<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

use EXME\Template\Lexer\Contract\TokenStreamInterface;
use EXME\Template\Parser\Contract\ParserInterface;
use EXME\Template\Parser\Node\Contract\NodeFactoryInterface;
use EXME\Template\Parser\Node\Contract\NodeInterface;

final class Parser implements ParserInterface
{
    public function __construct(
        private NodeFactoryInterface $nodeFactory,
    ){      
    }

    public function parse(TokenStreamInterface $tokens): NodeInterface
    {
        return $this->nodeFactory->create($tokens);       
    }
}
