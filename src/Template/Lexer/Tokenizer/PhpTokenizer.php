<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class PhpTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return ($context->mode === LexerMode::TEMPLATE || $context->mode === LexerMode::COMPONENT)
            && $context->current() === '{';
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;

        $context->moveNext();

        $start = $context->position;

        $this->consumeUntilPhpClose($context, $position);

        $text = substr(
            $context->source,
            $start,
            $context->position - $start,
        );

        if ($context->current() === '}' ) {
            $context->moveNext();
        }

        return new Token(
            type: TokenType::PHP,
            text: $text,
            position: $position,
        );
    }

    private function consumeUntilPhpClose(LexerContext $context, int $openingPosition): void
    {
        $depth = 1;
        $quote = null;
        while (!$context->isAtEnd()) {
            $current = $context->current();
            if ($quote !== null) {
                if ($current === "\\") { $context->moveNext(2); continue; }
                if ($current === $quote) { $quote = null; }
                $context->moveNext(); continue;
            }
            if ($current === "\"" || $current === chr(39)) { $quote = $current; $context->moveNext(); continue; }
            if ($current === "{") { ++$depth; }
            if ($current === "}" && --$depth === 0) { return; }
            $context->moveNext();
        }
        throw new \RuntimeException(sprintf('Unterminated PHP block at position %d', $openingPosition));
    }
}
