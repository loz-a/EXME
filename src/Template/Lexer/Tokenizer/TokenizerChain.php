<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;

final class TokenizerChain
{
    /**
     * @param list<TokenizerInterface> $tokenizers
     */
    public function __construct(
        private array $tokenizers,
    ){
    }

    public function tokenize(LexerContext $context): Token
    {
        if ($context->mode === LexerMode::COMPONENT) {
            $context->skipWhitespace();
        }

        foreach ($this->tokenizers as $tokenizer) {
            if (!$tokenizer->supports($context)) {
                continue;
            }

            return $tokenizer->tokenize($context);
        }

        throw new \RuntimeException(
            sprintf('Unexpected character "%s" at position %d', $context->current(), $context->position));
    }
}