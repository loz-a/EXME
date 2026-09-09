<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

use EXME\Template\Parser\Contract\TokenValidatorInterface;
use EXME\Template\Parser\Contract\ParserContextInterface;
use Override;

final class TokenValidator implements TokenValidatorInterface
{
    private array $validators;

    private ParserContextInterface $ctx;

    public function __construct(
        TokenValidatorInterface ...$validators
    ){
        $this->validators = $validators;
    }

    #[Override]
    public function setContext(ParserContextInterface $ctx): TokenValidatorInterface
    {
        foreach ($this->validators as $validator) {
            $validator->setContext($ctx);
        }

        return $this;
    }

    public function canValidate(): bool
    {
        return !$this->ctx->isEmpty();
    }

    public function validate(): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate();
        }
    }
}