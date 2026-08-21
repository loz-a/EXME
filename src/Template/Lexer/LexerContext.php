<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

final class LexerContext
{
    public function __construct(
        public readonly string $source,
        public int $position = 0,
    ) {}

    public function current(): ?string
    {
        return $this->source[$this->position] ?? null;
    }

    public function peek(int $offset = 1): ?string
    {
        return $this->source[$this->position + $offset] ?? null;
    }

    public function startsWith(string $value): bool
    {
        return substr($this->source, $this->position, strlen($value)) === $value;
    }

    public function isAtEnd(): bool
    {
        return $this->position >= strlen($this->source);
    }

    public function moveNext(int $steps = 1): void
    {
        $this->position += $steps;
    }

    public function skipWhitespace(): void
    {
        while (!$this->isAtEnd() && ctype_space($this->current())) {
            $this->moveNext();
        }
    }
}