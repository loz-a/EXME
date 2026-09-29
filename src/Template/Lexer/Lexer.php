<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Contract\TokenStreamInterface;
use EXME\Template\Lexer\Tokenizer\TokenizerChain;

final class Lexer
{
    public function __construct(
        private TokenizerChain $chain,
    ) {}

    public function tokenize(string $source, int $position = 0): TokenStreamInterface
    {
        $context = new LexerContext(source: $source, position: $position);
        $tokens = [];

        while (!$context->isAtEnd()) {
            $token = $this->chain->tokenize($context);

            if ($token->canTokenize) {
                $childTokens = $this->tokenize($token->text);
                $tokens = [ 
                    ...$tokens, 
                    ...$this->recalculateChildTokensPosition($childTokens->toArray(), $token->position),
                ];

                continue;
            }

            if ($token->isEmpty()) {
                continue;
            }

            $tokens[] = $token;
        }

        if ($context->mode === LexerMode::COMPONENT) {
            throw new \RuntimeException(sprintf('Unterminated component declaration at position %d', $context->position));
        }

        return new TokenStream(...$tokens);
    }

    private function recalculateChildTokensPosition(array $tokens, int $startPos): array
    {
        $result = [];

        foreach ($tokens as $token) {
            $result[] = new Token(
                type: $token->type,
                text: $token->text,
                position: $token->position + $startPos,
                canTokenize: $token->canTokenize,
            ); 
        }

        return $result;
    }
}
