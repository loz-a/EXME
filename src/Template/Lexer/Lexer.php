<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Contract\TokenCollectionInterface;
use EXME\Template\Lexer\Tokenizer\TokenizerChain;
use EXME\Template\Lexer\Validator\Contract\TokenValidatorInterface;

final class Lexer
{
    public function __construct(
        private TokenizerChain $chain,
        private TokenValidatorInterface $tokenValidator,
    ) {}

    public function tokenize(string $source, int $position = 0): TokenCollectionInterface
    {
        $context = new LexerContext(source: $source, position: $position);
        $tokens = [];

        while (!$context->isAtEnd()) {
            $token = $this->chain->tokenize($context);
            $this->tokenValidator->validate($token, $tokens);

            if ($token->canTokenize) {
                $childTokens = $this->tokenize($token->text);
                $tokens = [ 
                    ...$tokens, 
                    ...$this->recalculateChildTokensPosition($childTokens, $token->position)->toArray(),
                ];

                continue;
            }
            
            if (!$token->isEmpty()) {
                $tokens[] = $token;
            }
        }

        if ($context->mode === LexerMode::COMPONENT) {
            throw new \RuntimeException(sprintf('Unterminated component declaration at position %d', $context->position));
        }

        return new TokenCollection(...$tokens);
    }

    private function recalculateChildTokensPosition(TokenCollectionInterface $tokens, int $startPos): TokenCollectionInterface
    {
        $result = [];

        foreach ($tokens as $key => $token) {
            $result[$key] = new Token(
                type: $token->type,
                text: $token->text,
                position: $token->position + $startPos,
                canTokenize: $token->canTokenize,
            ); 
        }

        return new TokenCollection(...$result);
    }
}
