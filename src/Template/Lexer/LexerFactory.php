<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\Tokenizer\Component\BooleanTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\CloseTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\SelfCloseTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\StartTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\ClosingTagTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\NameTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\EqualsTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\IdentifierTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\NumberTokenizer;
use EXME\Template\Lexer\Tokenizer\Component\StringTokenizer;
use EXME\Template\Lexer\Tokenizer\HtmlTokenizer;
use EXME\Template\Lexer\Tokenizer\PhpTokenizer;
use EXME\Template\Lexer\Tokenizer\TextTokenizer;
use EXME\Template\Lexer\Tokenizer\TokenizerChain;

final class LexerFactory
{
    public function create(): Lexer
    {
        $tokenizer = new TokenizerChain([
            new PhpTokenizer(),

            new StartTokenizer(),
            new NameTokenizer(),
            new SelfCloseTokenizer(),
            new CloseTokenizer(),
            new ClosingTagTokenizer(),

            new HtmlTokenizer(),
            
            new EqualsTokenizer(),
            new BooleanTokenizer(),
            new NumberTokenizer(),
            new StringTokenizer(),
            new IdentifierTokenizer(),

            // Fallback
            new TextTokenizer(),
        ]);

        return new Lexer($tokenizer);
    }
}