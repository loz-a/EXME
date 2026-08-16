<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    /**
     * @param list<array{TokenType, string}> $expectedTokens
     */
    #[DataProvider('templateProvider')]
    public function testTokenizesTemplates(string $template, array $expectedTokens): void
    {
        $tokens = (new Lexer($template))->tokenize();

        self::assertSame(
            $expectedTokens,
            array_map(
                static fn (Token $token): array => [$token->type, $token->value],
                $tokens,
            ),
        );
    }

    /**
     * @return iterable<string, array{string, list<array{TokenType, string}>}>
     */
    public static function templateProvider(): iterable
    {
        yield 'self-closing tag without attributes' => [
            '<Greeting />',
            [
                [TokenType::TAG_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Greeting'],
                [TokenType::TAG_SELF_CLOSE, '/>'],
            ],
        ];

        yield 'self-closing tag with one attribute' => [
            '<Greeting name="Rasmus" />',
            [
                [TokenType::TAG_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Greeting'],
                [TokenType::IDENTIFIER, 'name'],
                [TokenType::EQUALS, '='],
                [TokenType::STRING, 'Rasmus'],
                [TokenType::TAG_SELF_CLOSE, '/>'],
            ],
        ];

        yield 'self-closing tag with multiple attributes' => [
            '<Greeting name="Rasmus" type="guest" />',
            [
                [TokenType::TAG_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Greeting'],
                [TokenType::IDENTIFIER, 'name'],
                [TokenType::EQUALS, '='],
                [TokenType::STRING, 'Rasmus'],
                [TokenType::IDENTIFIER, 'type'],
                [TokenType::EQUALS, '='],
                [TokenType::STRING, 'guest'],
                [TokenType::TAG_SELF_CLOSE, '/>'],
            ],
        ];
    }

    public function testSkipsWhitespaceBetweenTokens(): void
    {
        $tokens = (new Lexer(" \n<Greeting\t />\r\n"))->tokenize();

        self::assertSame(
            [TokenType::TAG_OPEN, TokenType::IDENTIFIER, TokenType::TAG_SELF_CLOSE],
            array_map(static fn (Token $token): TokenType => $token->type, $tokens),
        );
    }

    public function testRejectsInvalidCharacter(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected character "$" at position 10');

        (new Lexer('<Greeting $ />'))->tokenize();
    }

    public function testRejectsUnterminatedString(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unterminated string at position 15');

        (new Lexer('<Greeting name="Rasmus'))->tokenize();
    }

    #[DataProvider('invalidSelfClosingTagProvider')]
    public function testRejectsInvalidSelfClosingTag(string $template): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected ">" after "/" at position 10');

        (new Lexer($template))->tokenize();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidSelfClosingTagProvider(): iterable
    {
        yield 'space between slash and closing bracket' => ['<Greeting / >'];
        yield 'missing closing bracket' => ['<Greeting /'];
    }

    public function testAllowsUnderscoreAndHyphenInIdentifiers(): void
    {
        $tokens = (new Lexer('<_Greeting data-user="Rasmus" />'))->tokenize();

        self::assertSame(TokenType::IDENTIFIER, $tokens[1]->type);
        self::assertSame('_Greeting', $tokens[1]->value);
        self::assertSame(TokenType::IDENTIFIER, $tokens[2]->type);
        self::assertSame('data-user', $tokens[2]->value);
    }

    #[DataProvider('invalidIdentifierStartProvider')]
    public function testRejectsInvalidIdentifierStart(string $template, string $character): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Unexpected character "%s" at position 1', $character));

        (new Lexer($template))->tokenize();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidIdentifierStartProvider(): iterable
    {
        yield 'digit' => ['<1Greeting />', '1'];
        yield 'hyphen' => ['<-Greeting />', '-'];
    }
}
