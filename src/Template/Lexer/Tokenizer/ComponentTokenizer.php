<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class ComponentTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return $context->mode === LexerMode::TEMPLATE 
            && $context->current() === '<'
            && $this->isComponentStart($context);
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;

        $context->moveNext();

        return new Token(
            type: TokenType::COMPONENT_OPEN,
            text: '<',
            position: $position,
        );
    }

    private function isComponentStart(LexerContext $context): bool
    {
        $next = $context->peek();

        return $next !== null && ctype_upper($next);
    }
}