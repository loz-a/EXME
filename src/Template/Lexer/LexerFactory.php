<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\Tokenizer\ComponentCloseTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentSelfCloseTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentStartTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentClosingTagTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentNameTokenizer;
use EXME\Template\Lexer\Tokenizer\EqualsTokenizer;
use EXME\Template\Lexer\Tokenizer\HtmlTokenizer;
use EXME\Template\Lexer\Tokenizer\IdentifierTokenizer;
use EXME\Template\Lexer\Tokenizer\PhpTokenizer;
use EXME\Template\Lexer\Tokenizer\StringTokenizer;
use EXME\Template\Lexer\Tokenizer\TextTokenizer;
use EXME\Template\Lexer\Tokenizer\TokenizerChain;
use EXME\Template\Lexer\Validator\Token\ComponentTokenValidator;
use EXME\Template\Lexer\Validator\Token\HtmlTokenValidator;
use EXME\Template\Lexer\Validator\Token\PhpTokenValidator;
use EXME\Template\Lexer\Validator\Token\TextTokenValidator;
use EXME\Template\Lexer\Validator\TokenValidator;

final class LexerFactory
{
    public function create(): Lexer
    {
        $tokenizer = new TokenizerChain([
            new PhpTokenizer(),

            new ComponentStartTokenizer(),
            new ComponentNameTokenizer(),
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

        $tokenValidator = new TokenValidator(
            new ComponentTokenValidator(),
            new PhpTokenValidator(),
            new HtmlTokenValidator(),
            new TextTokenValidator(),
        );

        return new Lexer($tokenizer, $tokenValidator);
    }
}