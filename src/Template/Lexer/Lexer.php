<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Tokenizer\TokenizerChain;

final class Lexer
{
    public function __construct(
        private TokenizerChain $chain,
    ) {}

    public function tokenize(string $source, int $position = 0): array
    {
        $context = new LexerContext(source: $source, position: $position);
        $tokens = [];

        while (!$context->isAtEnd()) {
            $token = $this->chain->tokenize($context);

            if ($token->canTokenize) {
                $childTokens = $this->tokenize($token->text);
                $tokens = [ 
                    ...$tokens, 
                    ...$this->recalculateChildTokensPosition($childTokens, $token->position)
                ];

                continue;
            }
            
            if (!$token->isEmpty()) {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    private function recalculateChildTokensPosition(array $tokens, int $startPos): array
    {
        $result = [];

        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $result[$i] = new Token(
                type: $tokens[$i]->type,
                text: $tokens[$i]->text,
                position: $tokens[$i]->position + $startPos,
                canTokenize: $tokens[$i]->canTokenize,
            ); 
        }

        return $result;
    }
}
