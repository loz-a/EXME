<?php
// tests/ExampleTest.php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\TestCase;

final class SimpleTokenizingTest extends TestCase
{
    private Lexer $lexer;

    public function setUp(): void
    {
        $lexerFactory = new LexerFactory();
        $this->lexer = $lexerFactory->create();
    }
    
    public function testTokenizeComponent(): void
    {
        $tokens = $this->lexer->tokenize(
            '<Greeting name="Rasmus" type="guest" />',
        );

        self::assertCount(9, $tokens);

        self::assertSame(TokenType::COMPONENT_OPEN, $tokens[0]->type);
        self::assertSame('<', $tokens[0]->text);

        self::assertSame(TokenType::IDENTIFIER, $tokens[1]->type);
        self::assertSame('Greeting', $tokens[1]->text);

        self::assertSame(TokenType::IDENTIFIER, $tokens[2]->type);
        self::assertSame('name', $tokens[2]->text);

        self::assertSame(TokenType::EQUALS, $tokens[3]->type);
        self::assertSame('=', $tokens[3]->text);

        self::assertSame(TokenType::TEXT, $tokens[4]->type);
        self::assertSame('Rasmus', $tokens[4]->text);

        self::assertSame(TokenType::IDENTIFIER, $tokens[5]->type);
        self::assertSame('type', $tokens[5]->text);

        self::assertSame(TokenType::EQUALS, $tokens[6]->type);
        self::assertSame('=', $tokens[6]->text);

        self::assertSame(TokenType::TEXT, $tokens[7]->type);
        self::assertSame('guest', $tokens[7]->text);

        self::assertSame(TokenType::COMPONENT_SELF_CLOSE, $tokens[8]->type);
        self::assertSame('/>', $tokens[8]->text);
    }
}
