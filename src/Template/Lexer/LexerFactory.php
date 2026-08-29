<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\Tokenizer\ComponentCloseTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentSelfCloseTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentClosingTagTokenizer;
use EXME\Template\Lexer\Tokenizer\EqualsTokenizer;
use EXME\Template\Lexer\Tokenizer\HtmlTokenizer;
use EXME\Template\Lexer\Tokenizer\IdentifierTokenizer;
use EXME\Template\Lexer\Tokenizer\PhpTokenizer;
use EXME\Template\Lexer\Tokenizer\StringTokenizer;
use EXME\Template\Lexer\Tokenizer\TextTokenizer;
use EXME\Template\Lexer\Tokenizer\TokenizerChain;

final class LexerFactory
{
    public function create(): Lexer
    {
        $chain = new TokenizerChain([
            new PhpTokenizer(),

            new ComponentTokenizer(),
            new ComponentSelfCloseTokenizer(),
            new ComponentCloseTokenizer(),
            new ComponentClosingTagTokenizer(),

            new HtmlTokenizer(),
            
            new EqualsTokenizer(),
            new StringTokenizer(),
            new IdentifierTokenizer(),

            // Fallback
            new TextTokenizer(),
        ]);

        return new Lexer($chain);
    }
}