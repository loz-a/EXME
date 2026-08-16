<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string $value,
        public int $position,
    ) {
    }
}