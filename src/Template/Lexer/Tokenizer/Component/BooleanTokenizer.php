<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer\Component;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class BooleanTokenizer implements TokenizerInterface
{
    public function supports(LexerContext $context): bool
    {
        return $context->mode === LexerMode::COMPONENT 
            && $this->isBool($context)
            && $context->peek(-1) === '=';
    }

    public function tokenize(LexerContext $context): Token
    {
        $position = $context->position;

        $isTrue = $context->startsWith('true', isCaseInsensitive: true);
        $context->moveNext($isTrue ? 4 : 5);

        $isTrailingSpace = ctype_space($context->current()); 
        if (!$isTrailingSpace) {
            throw new \RuntimeException(
                sprintf('Expected a space after the %s argument value.', $isTrue ? 'true' : 'false'));
        }

        return new Token(
            type: TokenType::BOOL,
            text: $isTrue ? 'true' : 'false',
            position: $position,
        );
    }

    private function isBool(LexerContext $context): bool
    {
        return $context->startsWith('true', isCaseInsensitive: true)
            || $context->startsWith('false', isCaseInsensitive: true);
    }
}