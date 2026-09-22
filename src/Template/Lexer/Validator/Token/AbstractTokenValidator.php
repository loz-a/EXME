<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Validator\Token;

use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Validator\Contract\TokenValidatorInterface;
use RuntimeException;

abstract class AbstractTokenValidator implements TokenValidatorInterface
{
    /**
     * @param Token $token
     * @param Token[] $context
     */
    abstract public function validate(Token $token, array $context): void;

    protected function expect(Token $token, TokenType ...$expectedTokenTypes): void
    {
        $expectedTypes = array_map(
            static fn (TokenType $type): string => $type->name,
            $expectedTokenTypes
        );

        $isExpectationSuccessful = in_array($token->type->name, $expectedTypes);

        if (!$isExpectationSuccessful) {
            throw new RuntimeException(
                sprintf('Expected tokens are: %s, got %s', implode(', ', $expectedTypes), $token->type->name));        
        }
    }
}