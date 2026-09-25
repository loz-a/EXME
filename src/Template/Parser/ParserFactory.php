<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

use EXME\Template\Parser\Contract\ParserInterface;
use EXME\Template\Parser\Node\Attribute\AttributeFactory;
use EXME\Template\Parser\Node\Factory\NodeFactory;

final class ParserFactory
{
    public function create(): ParserInterface
    {
        $attributeFactory = new AttributeFactory();

        return new Parser(
            nodeFactory: new NodeFactory($attributeFactory),
            // resultBuilder: new ResultBuilder(),
        );
    }
}