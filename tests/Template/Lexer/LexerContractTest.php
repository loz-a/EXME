<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\TestCase;

final class LexerContractTest extends TestCase
{
    private Lexer $lexer;

    protected function setUp(): void
    {
        $this->lexer = new LexerFactory()->create();
    }

    public function testTokensReferToTheirOriginalAbsolutePositions(): void
    {
        $source = 'before <Greeting name="Hello {$name}!"><strong>Hi</strong></Greeting> after';

        foreach ($this->lexer->tokenize($source) as $token) {
            $offset = $token->type === TokenType::PHP ? 1 : 0;
            self::assertGreaterThanOrEqual(0, $token->position);
            self::assertSame($token->text, substr($source, $token->position + $offset, strlen($token->text)));
        }
    }

    public function testPhpTokenPositionIsItsOpeningBrace(): void
    {
        $source = 'text {$name} text';
        $token = array_values(array_filter(
            $this->lexer->tokenize($source),
            static fn (Token $token): bool => $token->type === TokenType::PHP,
        ))[0];

        self::assertSame(5, $token->position);
        self::assertSame('$name', $token->text);
    }

    public function testLexerProducesOnlyPublicTokenTypes(): void
    {
        $tokens = $this->lexer->tokenize('{ $name = "John"; }<Greeting name={$name} /><div>Hello</div>');

        self::assertContains(TokenType::PHP, array_map(static fn (Token $token): TokenType => $token->type, $tokens));
        self::assertContains(TokenType::HTML, array_map(static fn (Token $token): TokenType => $token->type, $tokens));
        foreach ($tokens as $token) {
            self::assertInstanceOf(TokenType::class, $token->type);
            self::assertNotSame('', $token->text);
        }
    }
}
