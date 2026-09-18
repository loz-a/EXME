<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Contract;

use Countable;
use EXME\Template\Lexer\Token;

interface TokenCollectionInterface extends Countable
{
    public function dequeue(): Token;

    public function isEmpty(): bool;

    public function toArray(): array;
}