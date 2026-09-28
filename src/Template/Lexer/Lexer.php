<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Contract\TokenStreamInterface;
use EXME\Template\Lexer\Tokenizer\TokenizerChain;
use Generator;

final class Lexer
{
    public function __construct(
        private TokenizerChain $chain,
    ) {}

    public function tokenize(string $source, int $position = 0): TokenStreamInterface
    {
        return TokenStream::fromIterator($this->generateTokens($source, $position));
    }

    /**
     * @return Generator<int, Token>
     */
    private function generateTokens(string $source, int $position = 0, int $positionOffset = 0): Generator
    {
        $context = new LexerContext(source: $source, position: $position);

        while (!$context->isAtEnd()) {
            $token = $this->chain->tokenize($context);

            if ($token->canTokenize) {
                yield from $this->generateTokens(
                    source: $token->text,
                    positionOffset: $token->position + $positionOffset,
                );

                continue;
            }

            if ($token->isEmpty()) {
                continue;
            }

            yield $this->offsetPosition($token, $positionOffset);
        }

        if ($context->mode === LexerMode::COMPONENT) {
            throw new \RuntimeException(sprintf(
                'Unterminated component declaration at position %d',
                $context->position + $positionOffset,
            ));
        }
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
