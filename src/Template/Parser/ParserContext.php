<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

use EXME\Template\Lexer\Token;
use EXME\Template\Parser\Contract\ParserContextInterface;
use Override;
use RuntimeException;
use Traversable;

use function array_filter;

class ParserContext implements ParserContextInterface
{
    /**
     * @var list<Token>
     */
    private readonly array $tokens;

    private int $position = 0;

    /**
     * @param list<Token> $tokens
     */
    public function __construct(Token ...$tokens)
    {
        $this->tokens = array_filter($tokens);
    }

    public function next(int $offset = 1): ?Token
    {
        return $this->tokens[$this->position + $offset] ?? null;
    }

    public function prev(int $offset = 1): ?Token
    {
        return $this->tokens[$this->position - $offset] ?? null;
    }

    public function current(): Token
    {
        return $this->tokens[$this->position];
    }

    public function hasNext(): bool
    {
        return isset($this->tokens[$this->position + 1]);
    }

    public function hasPrev(): bool
    {
        return isset($this->tokens[$this->position - 1]);
    }

    public function moveNext(): void
    {
        if (!$this->hasNext()) {
            throw new RuntimeException('Tokens are exhausted');
        }

        $this->position = $this->position + 1;
    }

    public function reset(): void
    {
        $this->position = 0;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return count($this->tokens) > 0;
    }

    #[Override]
    public function getIterator(): Traversable
    {
        foreach ($this->tokens as $token) {
            yield $token;
        }
    }

    #[Override]
    public function count(): int
    {
        return count($this->tokens);
    }
}