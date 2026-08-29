<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class HtmlTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        $isUnsupported = $context->mode !== LexerMode::TEMPLATE || $context->current() !== '<';
        if ($isUnsupported) {
            return false;
        }

        $next = $context->peek();

        return $next !== null && (ctype_lower($next) || $next === '/' || $next === '!');
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;

        $this->consumeUntilTagEnd($context);

        return new Token(
            type: TokenType::HTML,
            text: substr(
                $context->source,
                $position,
                $context->position - $position,
            ),
            position: $position,
        );
    }

    private function consumeUntilTagEnd(LexerContext $context): void
    {
        while (!$context->isAtEnd()) {
            $char = $context->current();

            if ($char === '>') {
                $context->moveNext();

                return;
            }

            $context->moveNext();
        }

        throw new \RuntimeException(
            sprintf('Unterminated HTML tag at position %d', $context->position));
    }
}