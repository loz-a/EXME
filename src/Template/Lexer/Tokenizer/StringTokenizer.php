<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class StringTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return $context->mode === LexerMode::COMPONENT 
            && $context->current() === '"';
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;

        $context->moveNext();

        $start = $context->position;

        while (!$context->isAtEnd() 
            && $context->current() !== '"'
        ) {
            $context->moveNext();
        }

        if ($context->isAtEnd()) {
            throw new \RuntimeException(
                sprintf('Unterminated string at position %d', $position));
        }

        $text = substr(
            $context->source,
            $start,
            $context->position - $start,
        );

        $context->moveNext();

        return new Token(
            type: TokenType::TEXT,
            text: $text,
            position: $position,
            canTokenize: true,
        );
    }
}