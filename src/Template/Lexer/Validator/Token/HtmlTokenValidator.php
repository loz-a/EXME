<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Validator\Token;

use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;

class HtmlTokenValidator extends AbstractTokenValidator
{
    public function validate(Token $token, array $context): void
    {
        if (!count($context)) {
            return;
        }

        $prevToken = array_last($context);

        if ($prevToken->type === TokenType::HTML) {
            $this->expect(
                $token,
                TokenType::COMPONENT_OPEN,
                TokenType::COMPONENT_CLOSE,
                TokenType::PHP,
                TokenType::TEXT  
            );
        }
    }
}