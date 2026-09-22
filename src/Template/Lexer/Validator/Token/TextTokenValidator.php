<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Validator\Token;

use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;

class TextTokenValidator extends AbstractTokenValidator
{
    public function validate(Token $token, array $context): void
    {
        if (!count($context)) {
            return;
        }

        $prevToken = array_last($context);

        if ($prevToken->type === TokenType::TEXT) {
            $this->expect(
                $token,
                TokenType::IDENTIFIER,
                TokenType::COMPONENT_OPEN,
                TokenType::COMPONENT_CLOSE,
                TokenType::COMPONENT_SELF_CLOSE,
                TokenType::COMPONENT_CLOSING_TAG,
                TokenType::HTML,
                TokenType::PHP,  
            );
        }
    }
}