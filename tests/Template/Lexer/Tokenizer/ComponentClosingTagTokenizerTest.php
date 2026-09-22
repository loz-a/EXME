<?php

declare(strict_types=1);

namespace EXMETests\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\TestCase;

final class ComponentClosingTagTokenizerTest extends TestCase
{
    private Lexer $lexer;
    
    public function setUp(): void
    {
        $this->lexer = new LexerFactory()->create();
    }    

    public function testTokenizesComponentClosingTag(): void
    {
        $tokens = $this->lexer->tokenize(
            '</User>',
        );

        $token = $tokens->dequeue();

        self::assertSame(
            TokenType::COMPONENT_CLOSING_TAG,
            $token->type,
        );

        self::assertSame(
            'User',
            $token->text,
        );
    }
}