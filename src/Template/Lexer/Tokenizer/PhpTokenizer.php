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
        $canBeTokenized = $context->mode === LexerMode::TEMPLATE || $context->mode === LexerMode::COMPONENT;
        return $canBeTokenized && $context->current() === '{';
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

    private function consumeUntilPhpClose(LexerContext $context, int $startPosition): void
    {
        $depth = 1;

        while (!$context->isAtEnd()) {
            $current = $context->current();
            
            if ($current === "{") { 
                ++$depth; 
            }

            $isEndPosition = $current === "}" && --$depth === 0;
            if ($isEndPosition) { 
                return; 
            }

            $context->moveNext();
        }

        throw new \RuntimeException(sprintf('Unterminated PHP block at position %d', $startPosition));
    }
}
