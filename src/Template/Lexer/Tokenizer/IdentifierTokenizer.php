<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class IdentifierTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return $context->mode === LexerMode::COMPONENT
            && $this->isIdentifierStart($context->current());
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;
        $start = $position;

        while (!$context->isAtEnd()) {
            if (!$this->isIdentifierPart($context->current())) {
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

    private function isIdentifierStart(string $char): bool
    {
        return ctype_alpha($char) || $char === '_';
    }

    private function isIdentifierPart(string $char): bool
    {
        return ctype_alnum($char) || $char === '_' || $char === '-';
    }
}