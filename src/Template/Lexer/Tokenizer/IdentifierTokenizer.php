<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class IdentifierTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        $char = $context->current();

        return $char !== null && (ctype_alpha($char) || $char === '_');
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;
        $start = $position;

        while (!$context->isAtEnd()) {
            $char = $context->current();
            $forbiddenChar = !ctype_alnum($char) && $char !== '_' && $char !== '-';

            if ($char === null || $forbiddenChar) {
                break;
            }

            $context->moveNext();
        }

        return new Token(
            type: TokenType::IDENTIFIER,
            text: substr(
                $context->source,
                $start,
                $context->position - $start,
            ),
            position: $position,
        );
    }
}