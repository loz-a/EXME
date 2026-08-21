<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Tokenizer\TokenizerChain;

final class Lexer
{
    public function __construct(
        private TokenizerChain $chain,
    ) {}

    public function tokenize(string $source): array
    {
        $context = new LexerContext($source);
        $tokens = [];

        while (!$context->isAtEnd()) {
            $tokens[] = $this->chain->tokenize($context);
        }

        return $tokens;
    }
}
