<?php
// tests/ExampleTest.php

declare(strict_types=1);

namespace EXMETests;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\TestCase;

final class SimpleTokenizingTest extends TestCase
{
    public function testTokenizeComponent(): void
    {
        $lexer = new Lexer(
            '<Greeting name="Rasmus" type="guest" />'
        );

        $tokens = $lexer->tokenize();

        self::assertCount(9, $tokens);

        self::assertSame(TokenType::TAG_OPEN, $tokens[0]->type);
        self::assertSame('<', $tokens[0]->value);

        self::assertSame(TokenType::IDENTIFIER, $tokens[1]->type);
        self::assertSame('Greeting', $tokens[1]->value);

        self::assertSame(TokenType::IDENTIFIER, $tokens[2]->type);
        self::assertSame('name', $tokens[2]->value);

        self::assertSame(TokenType::EQUALS, $tokens[3]->type);
        self::assertSame('=', $tokens[3]->value);

        self::assertSame(TokenType::STRING, $tokens[4]->type);
        self::assertSame('Rasmus', $tokens[4]->value);

        self::assertSame(TokenType::IDENTIFIER, $tokens[5]->type);
        self::assertSame('type', $tokens[5]->value);

        self::assertSame(TokenType::EQUALS, $tokens[6]->type);
        self::assertSame('=', $tokens[6]->value);

        self::assertSame(TokenType::STRING, $tokens[7]->type);
        self::assertSame('guest', $tokens[7]->value);

        self::assertSame(TokenType::TAG_SELF_CLOSE, $tokens[8]->type);
        self::assertSame('/>', $tokens[8]->value);
    }
}