<?php
// src/Lexer.php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use function strlen;

final class Lexer
{
    private int $position = 0;

    private readonly int $length;

    public function __construct(
        private readonly string $source,
    ) {
        $this->length = strlen($source);
    }

    /**
     * @return list<Token>
     */
    public function tokenize(): array
    {
        $tokens = [];

        while (!$this->isAtEnd()) {
            $this->skipWhitespace();

            if ($this->isAtEnd()) {
                break;
            }

            $tokens[] = $this->nextToken();
        }

        return $tokens;
    }

    private function nextToken(): Token
    {
        $char = $this->current();

        return match (true) {
            $char === '<' => $this->tagOpen(),
            $char === '/' => $this->tagSelfClose(),
            $char === '=' => $this->equals(),
            $char === '"' => $this->string(),
            $this->isIdentifierStart($char) => $this->identifier(),

            default => throw new \RuntimeException(
                sprintf(
                    'Unexpected character "%s" at position %d',
                    $char,
                    $this->position,
                ),
            ),
        };
    }

    private function tagOpen(): Token
    {
        $position = $this->position;

        $this->moveNext();

        return new Token(
            type: TokenType::TAG_OPEN,
            value: '<',
            position: $position,
        );
    }

    private function tagSelfClose(): Token
    {
        $position = $this->position;

        $this->moveNext();

        if ($this->isAtEnd() || $this->current() !== '>') {
            throw new \RuntimeException(
                sprintf(
                    'Expected ">" after "/" at position %d',
                    $position,
                ),
            );
        }

        $this->moveNext();

        return new Token(
            type: TokenType::TAG_SELF_CLOSE,
            value: '/>',
            position: $position,
        );
    }

    private function equals(): Token
    {
        $position = $this->position;

        $this->moveNext();

        return new Token(
            type: TokenType::EQUALS,
            value: '=',
            position: $position,
        );
    }

    private function string(): Token
    {
        $position = $this->position;

        $this->moveNext();

        $start = $this->position;

        while (!$this->isAtEnd() && $this->current() !== '"') {
            $this->moveNext();
        }

        if ($this->isAtEnd()) {
            throw new \RuntimeException(
                sprintf(
                    'Unterminated string at position %d',
                    $position,
                ),
            );
        }

        $value = substr(
            $this->source,
            $start,
            $this->position - $start,
        );

        $this->moveNext();

        return new Token(
            type: TokenType::STRING,
            value: $value,
            position: $position,
        );
    }

    private function identifier(): Token
    {
        $position = $this->position;
        $start = $this->position;

        while (
            !$this->isAtEnd()
            && $this->isIdentifierPart($this->current())
        ) {
            $this->moveNext();
        }

        return new Token(
            type: TokenType::IDENTIFIER,
            value: substr(
                $this->source,
                $start,
                $this->position - $start,
            ),
            position: $position,
        );
    }

    private function skipWhitespace(): void
    {
        while (
            !$this->isAtEnd()
            && ctype_space($this->current())
        ) {
            $this->moveNext();
        }
    }

    private function isIdentifierStart(string $char): bool
    {
        return ctype_alpha($char) || $char === '_';
    }

    private function isIdentifierPart(string $char): bool
    {
        return ctype_alnum($char)
            || $char === '_'
            || $char === '-';
    }

    private function current(): string
    {
        return $this->source[$this->position];
    }

    private function moveNext(): void
    {
        $this->position++;
    }

    private function isAtEnd(): bool
    {
        return $this->position >= $this->length;
    }
}