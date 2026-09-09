<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

use EXME\Html\Contract\HtmlInterface;
use EXME\Template\Parser\Contract\TokenValidatorInterface;
use EXME\Template\Parser\Contract\ParserContextInterface;
use EXME\Template\Parser\Contract\ParserInterface;

final class Parser implements ParserInterface
{
    public function __construct(
        private TokenValidatorInterface $tokenValidator,
        private HtmlResultBuilder $resultBuilder,
    ){      
    }

    public function parse(ParserContextInterface $ctx): HtmlInterface
    {
        $this->validateTokens($ctx);
        return $this->resultBuilder->build($ctx);


        // $this->position = 0;

        // $this->expect(TokenType::TAG_OPEN);

        // $name = $this->expect(TokenType::IDENTIFIER)->value;
        // $classDefinition = $this->runtimeDefinition->getClassDefinition($name);

        // $attributes = [];

        // while (!$this->isAtEnd() && $this->current()->type !== TokenType::TAG_SELF_CLOSE) {
        //     $attribute = $this->expect(TokenType::IDENTIFIER);

        //     if (array_key_exists($attribute->value, $attributes)) {
        //         throw new \RuntimeException(
        //             sprintf(
        //                 'Duplicate attribute "%s" at position %d',
        //                 $attribute->value,
        //                 $attribute->position,
        //             ),
        //         );
        //     }

        //     $this->expect(TokenType::EQUALS);

        //     $attributeValue = $this->expect(TokenType::STRING)->value;

        //     $attributes[$attribute->value] = $attributeValue;
        // }

        // $this->expect(TokenType::TAG_SELF_CLOSE);

        // if (!$this->isAtEnd()) {
        //     throw new \RuntimeException(
        //         sprintf(
        //             'Unexpected token "%s" at position %d',
        //             $this->current()->value,
        //             $this->current()->position,
        //         ),
        //     );
        // }

        // return new ComponentNode(
        //     name: $name,
        //     attributes: $attributes,
        // );
    }

    private function validateTokens(ParserContextInterface $ctx): void
    {
        $tokenValidator = $this->tokenValidator->setContext($ctx);

        $tokenValidator->validate();
        
        while ($ctx->hasNext()) {
            $ctx->moveNext();
            $tokenValidator->validate();
        }

        $ctx->reset();
    }
}
