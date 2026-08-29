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
        return $context->mode === LexerMode::TEMPLATE 
            && $context->current() === '{';
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;

        $context->moveNext();

        $start = $context->position;

        $this->consumeUntilPhpClose($context);

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

    private function consumeUntilPhpClose(LexerContext $context): void
    {
        while (!$context->isAtEnd()) {
            if ($context->current() == '}') {
                return;
            }

            $context->moveNext();
        }
    }
}