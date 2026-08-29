<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\PhpTokenizer;
use PHPUnit\Framework\TestCase;

final class PhpTokenizerTest extends TestCase
{
    public function testTokenizesBraceSyntaxAtAnAbsolutePosition(): void
    {
        $context = new LexerContext('prefix { $name }', position: 7);

        $token = (new PhpTokenizer())->tokenize($context);

        self::assertSame(TokenType::PHP, $token->type);
        self::assertSame(' $name ', $token->text);
        self::assertSame(7, $token->position);
        self::assertTrue($context->isAtEnd());
    }

    public function testCountsNestedPhpBraces(): void
    {
        $context = new LexerContext('{ function () { return true; } }');

        $token = (new PhpTokenizer())->tokenize($context);

        self::assertSame(' function () { return true; } ', $token->text);
        self::assertTrue($context->isAtEnd());
    }
}
