<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Contract;

use Countable;
use EXME\Template\Lexer\Token;
use IteratorAggregate;

interface ParserContextInterface extends IteratorAggregate, Countable
{
    public function next(int $offset = 1): ?Token;

    public function prev(int $offset = 1): ?Token;

    public function current(): Token;

    public function hasNext(): bool;

    public function hasPrev(): bool;

    public function moveNext(): void;

    public function reset(): void;

    public function isEmpty(): bool;
}