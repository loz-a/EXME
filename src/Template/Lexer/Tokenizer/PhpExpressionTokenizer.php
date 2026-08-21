<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class PhpExpressionTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return $context->mode === LexerMode::TEMPLATE 
            && $context->startsWith('<?=');
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;

        $context->moveNext(3);

        $start = $context->position;

        $this->consumeUntilPhpClose($context);

        $text = substr(
            $context->source,
            $start,
            $context->position - $start,
        );

        if ($context->startsWith('?>')) {
            $context->moveNext(2);
        }

        return new Token(
            type: TokenType::PHP_EXPRESSION,
            text: $text,
            position: $position,
        );
    }

    private function consumeUntilPhpClose(LexerContext $context): void
    {
        while (!$context->isAtEnd()) {
            if ($context->startsWith('?>')) {
                return;
            }

            $context->moveNext();
        }

        throw new \RuntimeException(
            sprintf('Unterminated PHP expression at position %d', $context->position));
    }
}