<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class TextTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return !$context->isAtEnd()
            && $context->current() !== '<';
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;
        $start = $position;

        while (!$context->isAtEnd() && $context->current() !== '<') {
            $context->moveNext();
        }

        return new Token(
            type: TokenType::TEXT,
            text: substr(
                $context->source,
                $start,
                $context->position - $start,
            ),
            position: $position,
        );
    }
}