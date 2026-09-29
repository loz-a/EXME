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
        $tokens = $this->generateTokens($source, $position);
        return new TokenStream(...$tokens);
    }

    private function generateTokens(string $source, int $position = 0, int $positionOffset = 0): array
    {
        $context = new LexerContext(source: $source, position: $position);
        $tokens = [];

        while (!$context->isAtEnd()) {
            $token = $this->chain->tokenize($context);

            if ($token->canTokenize) {
                $childTokens = $this->generateTokens(
                    source: $token->text,
                    positionOffset: $token->position + $positionOffset,
                );

                $tokens = [ ...$tokens, ...$childTokens ];
                continue;
            }

            if ($token->isEmpty()) {
                continue;
            }

            $tokens[] = $this->offsetPosition($token, $positionOffset);
        }

        return $tokens;
    }

    private function offsetPosition(Token $token, int $positionOffset): Token
    {
        if ($positionOffset === 0) {
            return $token;
        }

        return new Token(
            type: $token->type,
            text: $token->text,
            position: $token->position + $positionOffset,
            canTokenize: $token->canTokenize,
        );
    }
}
