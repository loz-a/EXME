<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\Tokenizer\Contract\TokenizerInterface;
use EXME\Template\Lexer\TokenType;

class DefaultTokenizer implements TokenizerInterface
{
    private int $position = 0;
    private int $length;

    public function __construct(private string $source)
    {
        $this->length = strlen($source);
    }

    /**
     * @return list<Token>
     */
    public function tokenize(LexerContext $context): array
    {
        $tokens = [];

        while (!$this->isAtEnd()) {
            $this->skipWhitespace();

            $tokens[] = $this->nextToken();
        }

        return $tokens;
    }

    private function nextToken(): Token
    {
        $char = $this->current();

        return match (true) {
            $this->isComponentStart($char) => $this->tagComponentOpen(),
            $char === '/' => $this->tagComponentSelfClose(),
            $char === '>' => $this->tagComponentClose(),
            $this->isPhpStart($char) => $this->tagPhp(),
            $char === '=' => $this->equals(),
            $char === '"' => $this->text(),
            $this->isIdentifierStart($char) => $this->identifier(),

            default => throw new \RuntimeException(
                sprintf('Unexpected character "%s" at position %d', $char, $this->position)),
        };
    }

    private function tagPhp(): Token
    {
        $position = $this->position;

        $this->moveNext(5);

        $start = $this->position;

        while (!$this->isAtEnd() && !$this->isPhpEnd($this->current())) {
            $this->moveNext();
        }

        $text = substr(
            $this->source,
            $start,
            $this->position - $start,
        );

        $this->moveNext();

        return new Token(
            type: TokenType::PHP,
            text: $text,
            position: $position,
        );
    }

    private function tagComponentOpen(): Token
    {
        $position = $this->position;

        $this->moveNext();

        return new Token(
            type: TokenType::COMPONENT_OPEN,
            text: '<',
            position: $position,
        );
    }

    private function tagComponentSelfClose(): Token
    {
        $position = $this->position;

        $this->moveNext();

        if ($this->isAtEnd() || $this->current() !== '>') {
            throw new \RuntimeException(
                sprintf('Expected ">" after "/" at position %d', $position));
        }

        $this->moveNext();

        return new Token(
            type: TokenType::COMPONENT_SELF_CLOSE,
            text: '/>',
            position: $position,
        );
    }

    private function tagComponentClose(): Token
    {
        $position = $this->position;

        $this->moveNext();

        return new Token(
            type: TokenType::COMPONENT_CLOSE,
            text: '>',
            position: $position,
        );
    }

    private function equals(): Token
    {
        $position = $this->position;

        $this->moveNext();

        return new Token(
            type: TokenType::EQUALS,
            text: '=',
            position: $position,
        );
    }

    private function  text(): Token
    {
        $position = $this->position;

        $this->moveNext();

        $start = $this->position;

        while (!$this->isAtEnd() && $this->current() !== '"') {
            $this->moveNext();
        }

        if ($this->isAtEnd()) {
            throw new \RuntimeException(
                sprintf('Unterminated string at position %d', $position));
        }

        $value = substr(
            $this->source,
            $start,
            $this->position - $start,
        );

        $this->moveNext();

        return new Token(
            type: TokenType::TEXT,
            text: $value,
            position: $position,
        );
    }

    private function identifier(): Token
    {
        $position = $this->position;
        $start = $this->position;

        while (!$this->isAtEnd() && $this->isIdentifierPart($this->current())) {
            $this->moveNext();
        }

        return new Token(
            type: TokenType::IDENTIFIER,
            text: substr(
                $this->source,
                $start,
                $this->position - $start,
            ),
            position: $position,
        );
    }

    private function isPhpStart(string $char): bool
    {
        if ('<' !== $char) {
            return false;
        }

        if (($this->length - 1) < ($this->position + 4)) {
            return false;
        }

        return '<?php' === substr($this->source, $this->position, 5);
    }

    private function isPhpEnd(string $char): bool
    {
        if ('?' !== $char) {
            return false;
        }

        $nextChar = $this->source[$this->position + 1] ?? null;

        return null !== $nextChar && $nextChar === '>';
    }

    private function isComponentStart(string $char): bool
    {
        if ('<' !== $char) {
            return false;
        }

        $nextChar = $this->source[$this->position + 1] ?? null;

        return null !== $nextChar && ctype_upper($nextChar);
    }

    private function isIdentifierStart(string $char): bool
    {
        return ctype_alpha($char) || $char === '_';
    }

    private function isIdentifierPart(string $char): bool
    {
        return ctype_alnum($char) || $char === '_' || $char === '-';
    }

    private function skipWhitespace(): void
    {
        while (!$this->isAtEnd() && ctype_space($this->current())) {
            $this->moveNext();
        }
    }

    private function current(): string
    {
        return $this->source[$this->position];
    }

    private function moveNext(int $steps = 1): void
    {
        $this->position = $this->position + $steps;
    }

    private function isAtEnd(): bool
    {
        return $this->position >= $this->length;
    }
}