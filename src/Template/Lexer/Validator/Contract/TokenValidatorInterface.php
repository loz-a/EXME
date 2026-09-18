<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Validator\Contract;

use EXME\Template\Lexer\Token;

interface TokenValidatorInterface
{
    /**
     * @param Token $token
     * @param Token[] $context
     */
    public function validate(Token $token, array $context): void;
}