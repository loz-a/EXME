<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use function strlen;

final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string $text,
        public int $position,
        public bool $canTokenize = false,
    ) {
    }

    public function isEmpty(): bool
    {
        return strlen($this->text) === 0;
    }
}