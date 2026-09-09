<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Contract;

interface TokenValidatorInterface
{
    public function setContext(ParserContextInterface $ctx): TokenValidatorInterface;

    public function validate(): void;
}