<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer\Contract;

interface TokenizerInterface
{
    public function tokenize(): array;
}