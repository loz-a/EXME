<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer\Component;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class NumberTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        $canSupport = $context->mode === LexerMode::COMPONENT
            && $context->peek(-1) === '=';

        $current = $context->current();

        if ($canSupport && is_numeric($current)) {
            return true;
        }
        
        return $canSupport && $current === '-' && is_numeric($context->peek());
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;
        $start = $context->position;
        
        $isNegative = $context->current() === '-';
        if ($isNegative) {
            $context->moveNext();
        }        

        while (!$context->isAtEnd() 
            && (is_numeric($context->current()) || $context->current() === '.')
        ) {
            $context->moveNext();
        }

        if ($context->isAtEnd()) {
            throw new \RuntimeException(
                sprintf('Unterminated numeric at position %d', $position));
        }

        $text = substr(
            $context->source,
            $start,
            $context->position - $start,
        );

        $context->moveNext();

        return new Token(
            type: TokenType::NUM,
            text: $text,
            position: $position,
        );
    }
}