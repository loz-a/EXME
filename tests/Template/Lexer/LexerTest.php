<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    private Lexer $lexer;

    public function setUp(): void
    {
        $lexerFactory = new LexerFactory();
        $this->lexer = $lexerFactory->create();
    }

    /** @param list<array{TokenType, string}> $expectedTokens */
    #[DataProvider("templateProvider")]
    public function testTokenizesTemplates(string $template, array $expectedTokens): void
    {
        $tokens = $this->lexer->tokenize($template);

        self::assertSame($expectedTokens, array_map(
            static fn (Token $token): array => [$token->type, $token->text],
            $tokens,
        ));
    }

    /** @return iterable<string, array{string, list<array{TokenType, string}>}> */
    public static function templateProvider(): iterable
    {
        yield "component without attributes" => ["<Greeting />", [
            [TokenType::COMPONENT_OPEN, "<"],
            [TokenType::IDENTIFIER, "Greeting"],
            [TokenType::COMPONENT_SELF_CLOSE, "/>"],
        ]];
        yield "component with attributes" => ["<Greeting name=\"Rasmus\" type=\"guest\" />", [
            [TokenType::COMPONENT_OPEN, "<"],
            [TokenType::IDENTIFIER, "Greeting"],
            [TokenType::IDENTIFIER, "name"],
            [TokenType::EQUALS, "="],
            [TokenType::TEXT, "Rasmus"],
            [TokenType::IDENTIFIER, "type"],
            [TokenType::EQUALS, "="],
            [TokenType::TEXT, "guest"],
            [TokenType::COMPONENT_SELF_CLOSE, "/>"],
        ]];
    }

    #[DataProvider("invalidTemplateProvider")]
    public function testRejectsInvalidTemplates(string $template, string $message): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains($message);
        $this->lexer->tokenize($template);
    }

    /** @return iterable<string, array{string, string}> */
    public static function invalidTemplateProvider(): iterable
    {
        yield "invalid component character" => ["<Greeting $ />", "Unexpected character \"$\" at position 10"];
        yield "unterminated quoted value" => ["<Greeting name=\"Rasmus", "Unterminated string at position 15"];
        yield "invalid self-closing suffix" => ["<Greeting / >", "Expected \">\" after \"/\" at position 10"];
    }
}
