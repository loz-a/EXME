<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Validator;

use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\Validator\Contract\TokenValidatorInterface;

final class TokenValidator implements TokenValidatorInterface
{
    private array $validators;

    public function __construct(
        TokenValidatorInterface ...$validators
    ){
        $this->validators = $validators;
    }

    public function validate(Token $token, array $context): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($token, $context);
        }
    }
}