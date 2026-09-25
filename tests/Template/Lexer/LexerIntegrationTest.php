<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LexerIntegrationTest extends TestCase
{
    private Lexer $lexer;

    protected function setUp(): void
    {
        $this->lexer = new LexerFactory()->create();
    }

    #[DataProvider('selfClosingComponentProvider')]
    public function testTokenizesSelfClosingComponents(string $source): void
    {
        $expected = [
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Submit'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
        ];

        $this->assertStream($source, $expected);
    }

    public static function selfClosingComponentProvider(): iterable
    {
        yield 'no whitespace' => ['<Submit/>'];
        yield 'single whitespace' => ['<Submit />'];
        yield 'many spaces' => ['<Submit     />'];
    }

    public function testTokenizesComponentWithNumericAttributes(): void
    {
        $this->assertStream("<Greeting age=15 />", [
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Greeting'],
            [TokenType::IDENTIFIER->name, 'age'],
            [TokenType::EQUALS->name, '='],
            [TokenType::NUM->name, '15'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
        ]);
    }

    public function testTokenizesComponentWithNegativeNumericAttributes(): void
    {
        $this->assertStream("<Greeting age=-15 />", [
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Greeting'],
            [TokenType::IDENTIFIER->name, 'age'],
            [TokenType::EQUALS->name, '='],
            [TokenType::NUM->name, '-15'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
        ]);
    }

    public function testTokenizesComponentWithNegativeFloatAttributes(): void
    {
        $this->assertStream("<Greeting age=-15.05 />", [
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Greeting'],
            [TokenType::IDENTIFIER->name, 'age'],
            [TokenType::EQUALS->name, '='],
            [TokenType::NUM->name, '-15.05'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
        ]);
    }

    public function testTokenizesComponentAttributesAcrossLines(): void
    {
        $this->assertStream("<Greeting\n    name=\"John\"\n    age=\"15\"\n    role=\"admin\"\n/>", [
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Greeting'],
            [TokenType::IDENTIFIER->name, 'name'],
            [TokenType::EQUALS->name, '='],
            [TokenType::TEXT->name, 'John'],
            [TokenType::IDENTIFIER->name, 'age'],
            [TokenType::EQUALS->name, '='],
            [TokenType::TEXT->name, '15'],
            [TokenType::IDENTIFIER->name, 'role'],
            [TokenType::EQUALS->name, '='],
            [TokenType::TEXT->name, 'admin'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
        ]);
    }

    public function testTokenizesPhpAttributesIncludingInterpolatedStrings(): void
    {
        $this->assertStream('<Greeting name={$user->name} age={$user->age} message="Hello {$user->name}!" />', [
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Greeting'],
            [TokenType::IDENTIFIER->name, 'name'],
            [TokenType::EQUALS->name, '='],
            [TokenType::PHP->name, '$user->name'],
            [TokenType::IDENTIFIER->name, 'age'],
            [TokenType::EQUALS->name, '='],
            [TokenType::PHP->name, '$user->age'],
            [TokenType::IDENTIFIER->name, 'message'],
            [TokenType::EQUALS->name, '='],
            [TokenType::TEXT->name, 'Hello '],
            [TokenType::PHP->name, '$user->name'],
            [TokenType::TEXT->name, '!'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
        ]);
    }

    public function testTokenizesWholeStandalonePhpBlock(): void
    {
        $source = "{\n    \$name = 'John';\n    \$age = 15;\n    \$isAdmin = false;\n}";
        $this->assertStream($source, [[TokenType::PHP->name, "\n    \$name = 'John';\n    \$age = 15;\n    \$isAdmin = false;\n"]]);
    }

    #[DataProvider('nestedPhpProvider')]
    public function testDoesNotClosePhpAtNestedBraces(string $source, string $expected): void
    {
        $this->assertStream($source, [[TokenType::PHP->name, $expected]]);
    }

    public static function nestedPhpProvider(): iterable
    {
        yield 'if block' => ["{ if (\$isAdmin) { \$role = 'admin'; } }", " if (\$isAdmin) { \$role = 'admin'; } "];
        yield 'if block without curly brackets' => ["{ if (\$isAdmin): \$role = 'admin'; }", " if (\$isAdmin): \$role = 'admin'; "];
        yield 'array literal' => ["{ \$data = ['name' => 'John', 'age' => 15]; }", " \$data = ['name' => 'John', 'age' => 15]; "];
        yield 'closure' => ["{ \$callback = function () { return true; }; }", " \$callback = function () { return true; }; "];
    }

    public function testReturnsToTemplateAfterSiblingComponentsInHtml(): void
    {
        $this->assertStream("<form>\n    <h1>Submit your choose:</h1>\n    <Submit />\n    <Cancel />\n</form>", [
            [TokenType::HTML->name, '<form>'],
            [TokenType::TEXT->name, "\n    "],
            [TokenType::HTML->name, '<h1>'],
            [TokenType::TEXT->name, 'Submit your choose:'],
            [TokenType::HTML->name, '</h1>'],
            [TokenType::TEXT->name, "\n    "],
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Submit'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
            [TokenType::TEXT->name, "\n    "],
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Cancel'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
            [TokenType::TEXT->name, "\n"],
            [TokenType::HTML->name, '</form>'],
        ]);
    }

    public function testTokenizesNestedComponentsAndClosingTags(): void
    {
        $source = '<Grid><Column><Header>Firstname:</Header><Row>John</Row><Row>Jane</Row></Column><Column><Header>Lastname:</Header><Row>Doe</Row><Row>Liu</Row></Column></Grid>';
        $tokens = $this->lexer->tokenize($source)->toArray();

        self::assertSame(
            ['Grid', 'Column', 'Header', 'Row', 'Row', 'Column', 'Header', 'Row', 'Row'],
            array_map(
                static fn (Token $token): string => $token->text, 
                array_values(
                    array_filter(
                        $tokens,
                        static fn (Token $token): bool => $token->type === TokenType::COMPONENT_NAME,
                    )
                )
            ),
        );

        self::assertSame(
            ['Header', 'Row', 'Row', 'Column', 'Header', 'Row', 'Row', 'Column', 'Grid'],
            array_map(
                static fn (Token $token): string => $token->text, 
                array_values(
                    array_filter(
                        $tokens,
                        static fn (Token $token): bool => $token->type === TokenType::COMPONENT_CLOSING_TAG,
                    )
                )
            ),
        );
    }

    public function testTokenizesComponentsMixedWithHtml(): void
    {
        $this->assertStream('<Card><div class="card">Hello</div></Card>', [
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Card'],
            [TokenType::COMPONENT_CLOSE->name, '>'],
            [TokenType::HTML->name, '<div class="card">'],
            [TokenType::TEXT->name, 'Hello'],
            [TokenType::HTML->name, '</div>'],
            [TokenType::COMPONENT_CLOSING_TAG->name, 'Card'],
        ]);
    }

    public function testTokenizesNestedComponentsInsideHtml(): void
    {
        $source = '<Grid><div class="container"><Column><Header>Firstname:</Header><Row>John</Row></Column></div></Grid>';
        $tokens = $this->lexer->tokenize($source)->toArray();

        self::assertSame(
            [
                TokenType::COMPONENT_OPEN, TokenType::HTML, TokenType::COMPONENT_OPEN, TokenType::COMPONENT_OPEN, 
                TokenType::COMPONENT_CLOSING_TAG, TokenType::COMPONENT_OPEN, TokenType::COMPONENT_CLOSING_TAG, TokenType::COMPONENT_CLOSING_TAG, 
                TokenType::HTML, TokenType::COMPONENT_CLOSING_TAG
            ],
            array_values(array_map(
                static fn (Token $token): TokenType => $token->type,
                array_values(
                    array_filter(
                        $tokens, 
                        static fn (Token $token): bool => $token->type !== TokenType::IDENTIFIER 
                                                    && $token->type !== TokenType::COMPONENT_NAME
                                                    && $token->type !== TokenType::COMPONENT_CLOSE
                                                    && $token->type !== TokenType::TEXT 
                                                    && $token->text !== '>'
                )),
            )),
        );
    }

    public function testKeepsPhpBlocksSeparateInsideComponentContent(): void
    {
        $source = "<Greeting>Hello {\$name}{ \$message = 'Welcome'; }{\$message}</Greeting>";
        $tokens = $this->lexer->tokenize($source)->toArray();

        self::assertSame(
            ['$name', " \$message = 'Welcome'; ", '$message'],
            array_map(
                static fn (Token $token): string => $token->text, 
                array_values(array_filter(
                    $tokens,
                    static fn (Token $token): bool => $token->type === TokenType::PHP,
                ))
            ),
        );

        $lastToken = array_last($tokens);
        self::assertSame(TokenType::COMPONENT_CLOSING_TAG, $lastToken->type);
        self::assertSame('Greeting', $lastToken->text);
    }

    public function testTokenizesComponentPhpHtmlAndComponentTransitions(): void
    {
        $source = "<Header />{ \$message = 'Hello'; }<div>{\$message}</div><Footer />";
        $this->assertStream($source, [
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Header'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
            [TokenType::PHP->name, " \$message = 'Hello'; "],
            [TokenType::HTML->name, '<div>'],
            [TokenType::PHP->name, '$message'],
            [TokenType::HTML->name, '</div>'],
            [TokenType::COMPONENT_OPEN->name, '<'],
            [TokenType::COMPONENT_NAME->name, 'Footer'],
            [TokenType::COMPONENT_SELF_CLOSE->name, '/>'],
        ]);
    }

    public function testUsesAbsolutePositionsForComponentsAndPhp(): void
    {
        $tokens = $this->lexer->tokenize('text <Submit /> text')->toArray();
        self::assertSame(
            [5, 6, 13], 
            array_map(
                static fn (Token $token): int => $token->position, 
                array_slice($tokens, 1, 3)
            )
        );

        $source = '<Grid><Column><Row>John</Row></Column></Grid>';
        $tokens = $this->lexer->tokenize($source)->toArray();
        
        self::assertSame(
            ['Grid', 'Column', 'Row', 'John', 'Row', 'Column', 'Grid'],
            array_map(
                static fn (Token $token): string => $token->text,
                array_values(array_filter($tokens, static fn (Token $token): bool => $token->type === TokenType::COMPONENT_NAME 
                            || $token->type === TokenType::COMPONENT_CLOSING_TAG || $token->type === TokenType::TEXT)),
            )
        );
        
        $source = 'text {$name} text';
        $tokens = $this->lexer->tokenize($source)->toArray();

        $php = array_values(
            array_filter(
                $tokens, 
                static fn (Token $token): bool => $token->type === TokenType::PHP
            )
        )[0];

        self::assertSame(5, $php->position);
        self::assertSame('$name', $php->text);
    }

    #[DataProvider('invalidTemplateProvider')]
    public function testRejectsInvalidLexerInput(string $source, string $message): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains($message);
        $this->lexer->tokenize($source);
    }

    public static function invalidTemplateProvider(): iterable
    {
        yield 'lone component opener' => ['<', 'Unexpected character "<" at position 0'];
        yield 'unterminated component' => ['<Submit', 'Unterminated component declaration at position 7'];
        yield 'incomplete component attribute' => ['<Submit =', 'Unterminated component declaration at position 9'];
        yield 'unterminated PHP' => ["{\n    \$name = 'John';", 'Unterminated PHP block at position 0'];
    }

    /** @param list<array{TokenType, string}> $expected */
    private function assertStream(string $source, array $expected): void
    {
        $tokens = $this->lexer->tokenize($source)->toArray();
        
        $actual = array_map(
            static fn (Token $token): array => [$token->type->name, $token->text],
            $tokens
        );
        
        self::assertSame($expected, $actual);
    }
}
