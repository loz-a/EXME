<?php

declare(strict_types=1);

namespace EXME\Tests\Unit\Template\Lexer;

use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TokenStreamTest extends TestCase
{
    public function testDefersLexingUntilATokenIsRequested(): void
    {
        $tokens = (new LexerFactory())->create()->tokenize('<Greeting /><');

        self::assertSame(TokenType::COMPONENT_OPEN, $tokens->dequeue()->type);
        self::assertSame(TokenType::COMPONENT_NAME, $tokens->dequeue()->type);
        self::assertSame(TokenType::COMPONENT_SELF_CLOSE, $tokens->dequeue()->type);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Unexpected character "<" at position 12');
        $tokens->isEmpty();
    }

    public function testCountMaterializesRemainingTokensWithoutConsumingThem(): void
    {
        $tokens = (new LexerFactory())->create()->tokenize('<Greeting />');

        self::assertCount(3, $tokens);
        self::assertSame(TokenType::COMPONENT_OPEN, $tokens->dequeue()->type);
    }
}
