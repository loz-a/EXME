<?php

declare(strict_types=1);

namespace EXME\Template\Parser\TokenValidator;

use EXME\Template\Lexer\TokenType;

class ComponentTokenValidator extends AbstractTokenValidator
{
    public function validate(): void
    {
        $ctx = $this->getContext();

        if (!$ctx->hasPrev()) {
            return;
        }

        $prevTokenType = $ctx->prev()->type;

        match (true) {
            $prevTokenType === TokenType::COMPONENT_OPEN => $this->expect(TokenType::IDENTIFIER),

            $this->isComponentName() => $this->expect([
                TokenType::IDENTIFIER, 
                TokenType::COMPONENT_CLOSE, 
                TokenType::COMPONENT_SELF_CLOSE,
            ]),
            
            $prevTokenType === TokenType::IDENTIFIER => $this->expect(TokenType::EQUALS),

            $prevTokenType === TokenType::EQUALS => $this->expect([TokenType::TEXT, TokenType::PHP]),
            
            $this->isClose() => $this->expect([
                TokenType::COMPONENT_OPEN,
                TokenType::COMPONENT_CLOSE,
                TokenType::HTML,
                TokenType::PHP,
                TokenType::TEXT                
            ]),            
        };
    }

    private function isComponentName(): bool
    {
        $ctx = $this->getContext();
        
        if ($ctx->prev(2)->type !== TokenType::COMPONENT_OPEN) {
            return false;
        }

        $prevToken = $ctx->prev();
        return $prevToken->type === TokenType::IDENTIFIER && ctype_upper($prevToken->text);
    }

    private function isClose(): bool
    {
        $prevTokenType = $this->getContext()->prev()->type;
        return $prevTokenType === TokenType::COMPONENT_CLOSE || TokenType::COMPONENT_SELF_CLOSE;
    }
}