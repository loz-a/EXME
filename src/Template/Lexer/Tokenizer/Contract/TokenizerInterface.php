<?php

declare(strict_types=1);

namespace EXME\Template\Lexer\Tokenizer\Contract;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\Token;

interface TokenizerInterface
{
    public function supports(LexerContext $context): bool;

    public function tokenize(LexerContext $context): Token;
}