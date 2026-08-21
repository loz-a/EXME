<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class ComponentCloseTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return $context->current() === '>';
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;

        $context->moveNext();

        return new Token(
            type: TokenType::COMPONENT_CLOSE,
            text: '>',
            position: $position,
        );
    }
}