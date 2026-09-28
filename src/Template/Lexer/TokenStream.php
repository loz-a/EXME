<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Contract\TokenStreamInterface;
use Generator;
use Iterator;
use Override;
use SplQueue;
use UnderflowException;

use function iterator_to_array;

final class TokenStream implements TokenStreamInterface
{
    /** @var Iterator<int, Token> */
    private Iterator $tokens;

    /** @var SplQueue<Token> */
    private SplQueue $buffer;

    private bool $started = false;

    private bool $exhausted = false;

    public function __construct(Token ...$tokens)
    {
        $this->tokens = self::fromTokens(array_values($tokens));
        $this->buffer = new SplQueue();
    }

    /**
     * @param Iterator<int, Token> $tokens
     */
    public static function fromIterator(Iterator $tokens): self
    {
        $stream = new self();
        $stream->tokens = $tokens;

        return $stream;
    }

    #[Override]
    public function peek(): ?Token
    {
        return $this->fillBuffer() ? $this->buffer->offsetGet(0) : null;
    }

    #[Override]
    public function dequeue(): Token
    {
        $token = $this->peek();

        if ($token === null) {
            throw new UnderflowException('Tokens are exhausted.');
        }

        return $this->buffer->dequeue();
    }

    #[Override]
    public function count(): int
    {
        $this->materialize();

        return $this->buffer->count();
    }

    /** @return list<Token> */
    #[Override]
    public function toArray(): array
    {
        $this->materialize();

        return iterator_to_array($this->buffer, false);
    }

    #[Override]
    public function isEmpty(): bool
    {
        return !$this->fillBuffer();
    }

    /**
     * @param array<int, Token> $tokens
     * @return Generator<int, Token>
     */
    private static function fromTokens(array $tokens): Generator
    {
        yield from $tokens;
    }

    private function fillBuffer(): bool
    {
        if (!$this->buffer->isEmpty()) {
            return true;
        }

        if ($this->exhausted) {
            return false;
        }

        if ($this->started) {
            $this->tokens->next();
        } else {
            $this->tokens->rewind();
            $this->started = true;
        }

        if (!$this->tokens->valid()) {
            $this->exhausted = true;

            return false;
        }

        $this->buffer->enqueue($this->tokens->current());

        return true;
    }

    private function materialize(): void
    {
        while ($this->fillBuffer()) {
            $this->tokens->next();

            if (!$this->tokens->valid()) {
                $this->exhausted = true;

                return;
            }

            $this->buffer->enqueue($this->tokens->current());
        }
    }
}
