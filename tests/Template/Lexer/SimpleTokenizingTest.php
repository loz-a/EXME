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

        $token = $tokens->dequeue();
        self::assertSame(TokenType::COMPONENT_OPEN, $token->type);
        self::assertSame('<', $token->text);

        $token = $tokens->dequeue();
        self::assertSame(TokenType::COMPONENT_NAME, $token->type);
        self::assertSame('Greeting', $token->text);

        $token = $tokens->dequeue();
        self::assertSame(TokenType::IDENTIFIER, $token->type);
        self::assertSame('name', $token->text);

        $token = $tokens->dequeue();
        self::assertSame(TokenType::EQUALS, $token->type);
        self::assertSame('=', $token->text);

        $token = $tokens->dequeue();
        self::assertSame(TokenType::TEXT, $token->type);
        self::assertSame('Rasmus', $token->text);

        $token = $tokens->dequeue();
        self::assertSame(TokenType::IDENTIFIER, $token->type);
        self::assertSame('type', $token->text);

        $token = $tokens->dequeue();
        self::assertSame(TokenType::EQUALS, $token->type);
        self::assertSame('=', $token->text);

        $token = $tokens->dequeue();
        self::assertSame(TokenType::TEXT, $token->type);
        self::assertSame('guest', $token->text);

        $token = $tokens->dequeue();
        self::assertSame(TokenType::COMPONENT_SELF_CLOSE, $token->type);
        self::assertSame('/>', $token->text);
    }
}
