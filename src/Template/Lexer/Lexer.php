<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Tokenizer\Tokenizer;

final class Lexer
{
    /**
     * @return list<Token>
     */
    public function tokenize(string $source): array
    {
        return new Tokenizer($source)->tokenize();
    }
}
