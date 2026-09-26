<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use function substr;
use function strlen;
use function ctype_space;
use function strtolower;

final class LexerContext
{
    public function __construct(
        public readonly string $source,
        public int $position = 0,
        public LexerMode $mode = LexerMode::TEMPLATE,
    ) {}

    public function current(): ?string
    {
        return $this->source[$this->position] ?? null;
    }

    public function peek(int $offset = 1): ?string
    {
        return $this->source[$this->position + $offset] ?? null;
    }

    public function startsWith(string $value, $isCaseInsensitive = false): bool
    {
        if ($isCaseInsensitive) {
            return strtolower(substr($this->source, $this->position, strlen($value))) === strtolower($value);
        }
            
        return substr($this->source, $this->position, strlen($value)) === $value;
    }

    public function isAtEnd(): bool
    {
        return $this->position >= strlen($this->source);
    }

    public function moveNext(int $steps = 1): void
    {
        $invalidPosition = ($this->position + $steps) > strlen($this->source);
        
        if ($invalidPosition) {
            throw new \RuntimeException(
                'Trying to reach a position beyond the end of the source.');
        }

        $this->position += $steps;
    }

    public function skipWhitespace(): void
    {
        while (!$this->isAtEnd() && ctype_space($this->current())) {
            $this->moveNext();
        }
    }
}