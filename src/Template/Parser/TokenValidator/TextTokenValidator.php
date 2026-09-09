<?php

declare(strict_types=1);

namespace EXME\Template\Parser\TokenValidator;

use EXME\Template\Lexer\TokenType;

class TextTokenValidator extends AbstractTokenValidator
{
    public function validate(): void
    {
        $ctx = $this->getContext();

        if (!$ctx->hasPrev()) {
            return;
        }

        $prevTokenType = $ctx->prev()->type;

        if ($prevTokenType === TokenType::HTML) {
            $this->expect([
                TokenType::COMPONENT_OPEN,
                TokenType::COMPONENT_CLOSE,
                TokenType::HTML,
                TokenType::PHP,  
            ]);
        }
    }
}