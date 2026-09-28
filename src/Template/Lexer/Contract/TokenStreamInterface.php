<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Contract;

use Countable;
use EXME\Template\Lexer\Token;

interface TokenStreamInterface extends Countable
{
    public function peek(): ?Token;

    public function dequeue(): Token;

    public function isEmpty(): bool;

    /**
     * Materializes the remaining tokens.
     *
     * @return list<Token>
     */
    public function toArray(): array;
}
