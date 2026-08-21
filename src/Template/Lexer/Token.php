<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use function strlen;
use function trim;

final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string $text,
        public int $position,
    ) {
    }

    public function isEmpty(): bool
    {
        return strlen(trim($this->text)) === 0;
    }
}