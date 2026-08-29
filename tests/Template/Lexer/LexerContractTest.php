<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LexerContractTest extends TestCase
{
    private Lexer $lexer;

    protected function setUp(): void
    {
        $this->lexer = new LexerFactory()->create();
    }

    /**
     * Every token must point to the exact text in the original source.
     *
     * This is one of the most important Lexer invariants.
     */
    #[DataProvider('templateProvider')]
    public function testTokenTextMatchesOriginalSource(string $source): void
    {
        $tokens = $this->lexer->tokenize($source);

        foreach ($tokens as $token) {
            self::assertInstanceOf(Token::class, $token);

            self::assertGreaterThanOrEqual(
                0,
                $token->position,
                'Token position must not be negative.',
            );

            self::assertLessThanOrEqual(
                strlen($source),
                $token->position + strlen($token->text),
                sprintf(
                    'Token "%s" exceeds the original source boundaries.',
                    $token->text,
                ),
            );

            self::assertSame(
                $token->text,
                substr(
                    $source,
                    $token->position,
                    strlen($token->text),
                ),
                sprintf(
                    'Token text does not match original source at position %d.',
                    $token->position,
                ),
            );
        }
    }

    /**
     * Token positions must be absolute positions in the original source.
     *
     * Tokens must be ordered exactly as their corresponding lexical
     * fragments appear in the source.
     */
    #[DataProvider('templateProvider')]
    public function testTokenPositionsAreAbsoluteAndOrdered(string $source): void
    {
        $tokens = $this->lexer->tokenize($source);

        $previousEnd = 0;

        foreach ($tokens as $token) {
            self::assertGreaterThanOrEqual(
                $previousEnd,
                $token->position,
                sprintf(
                    'Token at position %d overlaps a previous token.',
                    $token->position,
                ),
            );

            $previousEnd = $token->position + strlen($token->text);
        }
    }

    /**
     * The Lexer must produce only TokenType values defined by its contract.
     */
    #[DataProvider('templateProvider')]
    public function testEveryTokenHasValidTokenType(string $source): void
    {
        $tokens = $this->lexer->tokenize($source);

        foreach ($tokens as $token) {
            self::assertInstanceOf(TokenType::class, $token->type);
        }
    }

    /**
     * Empty tokens must never be returned by the Lexer.
     */
    #[DataProvider('templateProvider')]
    public function testLexerDoesNotReturnEmptyTokens(string $source): void
    {
        $tokens = $this->lexer->tokenize($source);

        foreach ($tokens as $token) {
            self::assertNotSame(
                '',
                $token->text,
                'Lexer must not return empty tokens.',
            );
        }
    }

    /**
     * The Lexer must support every public lexical category.
     *
     * This test protects the TokenType contract from accidental removal
     * or tokenizer regressions.
     */
    public function testLexerSupportsAllPublicTokenTypes(): void
    {
        $source = <<<'PHP'
            {$name = 'John'}

            <Greeting message="Hello {$name}!">
                Hello
            </Greeting>

            <Submit />
            PHP;

        $tokens = $this->lexer->tokenize($source);

        $types = array_unique(
            array_map(
                static fn (Token $token): TokenType => $token->type,
                $tokens,
            ),
            SORT_REGULAR,
        );

        self::assertContains(TokenType::PHP, $types);
        self::assertContains(TokenType::PHP, $types);

        self::assertContains(TokenType::COMPONENT_OPEN, $types);
        self::assertContains(TokenType::COMPONENT_CLOSE, $types);
        self::assertContains(TokenType::COMPONENT_SELF_CLOSE, $types);

        self::assertContains(TokenType::IDENTIFIER, $types);
        self::assertContains(TokenType::EQUALS, $types);

        self::assertContains(TokenType::TEXT, $types);
        self::assertContains(TokenType::HTML, $types);
    }

    /**
     * Recursive tokenization must preserve positions relative to
     * the original source.
     */
    public function testNestedTokensKeepAbsolutePositions(): void
    {
        $source = 'before <Greeting name="{$name}">Hello</Greeting> after';

        $tokens = $this->lexer->tokenize($source);

        foreach ($tokens as $token) {
            self::assertSame(
                $token->text,
                substr(
                    $source,
                    $token->position,
                    strlen($token->text),
                ),
                sprintf(
                    'Nested token "%s" has an invalid absolute position %d.',
                    $token->text,
                    $token->position,
                ),
            );
        }

        self::assertSame(
            '<',
            $tokens[1]->text,
        );

        self::assertSame(
            7,
            $tokens[1]->position,
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function templateProvider(): iterable
    {
        yield 'plain text' => [
            'Hello World',
        ];

        yield 'self closing component' => [
            '<Greeting />',
        ];

        yield 'component with attributes' => [
            '<Greeting name="John" age="15" />',
        ];

        yield 'nested components' => [
            '<Grid><Column><Row>John</Row></Column></Grid>',
        ];

        yield 'component with PHP expression' => [
            '<Greeting name="{$user->name}" />',
        ];

        yield 'PHP block' => [
            '{ if ($isAdmin): }Admin{ endif }',
        ];

        yield 'HTML mixed with components' => [
            '<div><Greeting />Hello</div>',
        ];

        yield 'complete template' => [
            <<<'PHP'
                {
                    $name = 'John';
                }

                <Greeting name="{$name}">
                    <strong>Hello</strong>
                </Greeting>
                PHP,
        ];
    }
}