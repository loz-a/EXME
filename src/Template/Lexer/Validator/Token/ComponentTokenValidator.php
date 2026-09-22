<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Validator\Token;

use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;

class ComponentTokenValidator extends AbstractTokenValidator
{
    public function validate(Token $token, array $context): void
    {
        if (!count($context)) {
            return;
        }

        $prevToken = array_last($context);

        match ($prevToken->type) {
             TokenType::COMPONENT_OPEN => $this->expect(
                $token, 
                TokenType::COMPONENT_NAME
            ),

            TokenType::COMPONENT_NAME => $this->expect(
                $token,
                TokenType::IDENTIFIER, 
                TokenType::COMPONENT_CLOSE, 
                TokenType::COMPONENT_SELF_CLOSE,
            ),
            
            TokenType::IDENTIFIER => $this->expect(
                $token, 
                TokenType::EQUALS
            ),

            TokenType::EQUALS => $this->expect(
                $token, 
                TokenType::TEXT, 
                TokenType::PHP
            ),
            
            TokenType::COMPONENT_CLOSE,
            TokenType::COMPONENT_SELF_CLOSE => $this->expect(
                $token,
                TokenType::COMPONENT_OPEN,
                TokenType::COMPONENT_CLOSING_TAG,
                TokenType::HTML,
                TokenType::PHP,
                TokenType::TEXT                
            ),

            TokenType::COMPONENT_CLOSING_TAG => $this->expect(
                $token,
                TokenType::COMPONENT_CLOSING_TAG,
                TokenType::COMPONENT_OPEN,
                TokenType::HTML,
                TokenType::PHP,
                TokenType::TEXT, 
            ),
        
            default => false
        };
    }
}