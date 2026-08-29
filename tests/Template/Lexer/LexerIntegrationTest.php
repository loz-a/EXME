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
        $this->assertStream($source, [
            [TokenType::COMPONENT_OPEN, '<'],
            [TokenType::IDENTIFIER, 'Submit'],
            [TokenType::COMPONENT_SELF_CLOSE, '/>'],
        ]);
    }

    public static function selfClosingComponentProvider(): iterable
    {
        yield 'no whitespace' => ['<Submit/>'];
        yield 'single whitespace' => ['<Submit />'];
        yield 'many spaces' => ['<Submit     />'];
    }

    public function testTokenizesComponentAttributesAcrossLines(): void
    {
        $this->assertStream("<Greeting\n    name=\"John\"\n    age=\"15\"\n    role=\"admin\"\n/>", [
            [TokenType::COMPONENT_OPEN, '<'],
            [TokenType::IDENTIFIER, 'Greeting'],
            [TokenType::IDENTIFIER, 'name'],
            [TokenType::EQUALS, '='],
            [TokenType::TEXT, 'John'],
            [TokenType::IDENTIFIER, 'age'],
            [TokenType::EQUALS, '='],
            [TokenType::TEXT, '15'],
            [TokenType::IDENTIFIER, 'role'],
            [TokenType::EQUALS, '='],
            [TokenType::TEXT, 'admin'],
            [TokenType::COMPONENT_SELF_CLOSE, '/>'],
        ]);
    }

    public function testTokenizesPhpAttributesIncludingInterpolatedStrings(): void
    {
        $this->assertStream('<Greeting name={$user->name} age={$user->age} message="Hello {$user->name}!" />', [
            [TokenType::COMPONENT_OPEN, '<'],
            [TokenType::IDENTIFIER, 'Greeting'],
            [TokenType::IDENTIFIER, 'name'],
            [TokenType::EQUALS, '='],
            [TokenType::PHP, '$user->name'],
            [TokenType::IDENTIFIER, 'age'],
            [TokenType::EQUALS, '='],
            [TokenType::PHP, '$user->age'],
            [TokenType::IDENTIFIER, 'message'],
            [TokenType::EQUALS, '='],
            [TokenType::TEXT, 'Hello '],
            [TokenType::PHP, '$user->name'],
            [TokenType::TEXT, '!'],
            [TokenType::COMPONENT_SELF_CLOSE, '/>'],
        ]);
    }

    public function testTokenizesWholeStandalonePhpBlock(): void
    {
        $source = "{\n    \$name = 'John';\n    \$age = 15;\n    \$isAdmin = false;\n}";
        $this->assertStream($source, [[TokenType::PHP, "\n    \$name = 'John';\n    \$age = 15;\n    \$isAdmin = false;\n"]]);
    }

    #[DataProvider('nestedPhpProvider')]
    public function testDoesNotClosePhpAtNestedBraces(string $source, string $php): void
    {
        $this->assertStream($source, [[TokenType::PHP, $php]]);
    }

    public static function nestedPhpProvider(): iterable
    {
        yield 'if block' => ["{ if (\$isAdmin) { \$role = 'admin'; } }", " if (\$isAdmin) { \$role = 'admin'; } "];
        yield 'array literal' => ["{ \$data = ['name' => 'John', 'age' => 15]; }", " \$data = ['name' => 'John', 'age' => 15]; "];
        yield 'closure' => ["{ \$callback = function () { return true; }; }", " \$callback = function () { return true; }; "];
    }

    public function testReturnsToTemplateAfterSiblingComponentsInHtml(): void
    {
        $this->assertStream("<form>\n    <h1>Submit your choose:</h1>\n    <Submit />\n    <Cancel />\n</form>", [
            [TokenType::HTML, '<form>'],
            [TokenType::TEXT, "\n    "],
            [TokenType::HTML, '<h1>'],
            [TokenType::TEXT, 'Submit your choose:'],
            [TokenType::HTML, '</h1>'],
            [TokenType::TEXT, "\n    "],
            [TokenType::COMPONENT_OPEN, '<'],
            [TokenType::IDENTIFIER, 'Submit'],
            [TokenType::COMPONENT_SELF_CLOSE, '/>'],
            [TokenType::TEXT, "\n    "],
            [TokenType::COMPONENT_OPEN, '<'],
            [TokenType::IDENTIFIER, 'Cancel'],
            [TokenType::COMPONENT_SELF_CLOSE, '/>'],
            [TokenType::TEXT, "\n"],
            [TokenType::HTML, '</form>'],
        ]);
    }

    public function testTokenizesNestedComponentsAndClosingTags(): void
    {
        $source = '<Grid><Column><Header>Firstname:</Header><Row>John</Row><Row>Jane</Row></Column><Column><Header>Lastname:</Header><Row>Doe</Row><Row>Liu</Row></Column></Grid>';
        $tokens = $this->lexer->tokenize($source);

        self::assertSame(
            ['Grid', 'Column', 'Header', 'Row', 'Row', 'Column', 'Header', 'Row', 'Row'],
            array_map(static fn (Token $token): string => $token->text, array_values(array_filter(
                $tokens,
                static fn (Token $token): bool => $token->type === TokenType::IDENTIFIER,
            ))),
        );
        self::assertSame(
            ['</Header>', '</Row>', '</Row>', '</Column>', '</Header>', '</Row>', '</Row>', '</Column>', '</Grid>'],
            array_map(static fn (Token $token): string => $token->text, array_values(array_filter(
                $tokens,
                static fn (Token $token): bool => $token->type === TokenType::COMPONENT_CLOSE && $token->text !== '>',
            ))),
        );
    }

    public function testTokenizesComponentsMixedWithHtml(): void
    {
        $this->assertStream('<Card><div class="card">Hello</div></Card>', [
            [TokenType::COMPONENT_OPEN, '<'],
            [TokenType::IDENTIFIER, 'Card'],
            [TokenType::COMPONENT_CLOSE, '>'],
            [TokenType::HTML, '<div class="card">'],
            [TokenType::TEXT, 'Hello'],
            [TokenType::HTML, '</div>'],
            [TokenType::COMPONENT_CLOSE, '</Card>'],
        ]);
    }

    public function testTokenizesNestedComponentsInsideHtml(): void
    {
        $source = '<Grid><div class="container"><Column><Header>Firstname:</Header><Row>John</Row></Column></div></Grid>';
        $tokens = $this->lexer->tokenize($source);

        self::assertSame(
            [TokenType::COMPONENT_OPEN, TokenType::HTML, TokenType::COMPONENT_OPEN, TokenType::COMPONENT_OPEN, TokenType::COMPONENT_CLOSE, TokenType::COMPONENT_OPEN, TokenType::COMPONENT_CLOSE, TokenType::COMPONENT_CLOSE, TokenType::HTML, TokenType::COMPONENT_CLOSE],
            array_values(array_map(
                static fn (Token $token): TokenType => $token->type,
                array_values(array_filter($tokens, static fn (Token $token): bool => $token->type !== TokenType::IDENTIFIER && $token->type !== TokenType::TEXT && $token->text !== '>')),
            )),
        );
    }

    public function testKeepsPhpBlocksSeparateInsideComponentContent(): void
    {
        $source = "<Greeting>Hello {\$name}{ \$message = 'Welcome'; }{\$message}</Greeting>";
        $tokens = $this->lexer->tokenize($source);

        self::assertSame(
            ['$name', " \$message = 'Welcome'; ", '$message'],
            array_map(static fn (Token $token): string => $token->text, array_values(array_filter(
                $tokens,
                static fn (Token $token): bool => $token->type === TokenType::PHP,
            ))),
        );
        self::assertSame('</Greeting>', $tokens[array_key_last($tokens)]->text);
    }

    public function testTokenizesComponentPhpHtmlAndComponentTransitions(): void
    {
        $source = "<Header />{ \$message = 'Hello'; }<div>{\$message}</div><Footer />";
        $this->assertStream($source, [
            [TokenType::COMPONENT_OPEN, '<'],
            [TokenType::IDENTIFIER, 'Header'],
            [TokenType::COMPONENT_SELF_CLOSE, '/>'],
            [TokenType::PHP, " \$message = 'Hello'; "],
            [TokenType::HTML, '<div>'],
            [TokenType::PHP, '$message'],
            [TokenType::HTML, '</div>'],
            [TokenType::COMPONENT_OPEN, '<'],
            [TokenType::IDENTIFIER, 'Footer'],
            [TokenType::COMPONENT_SELF_CLOSE, '/>'],
        ]);
    }

    public function testUsesAbsolutePositionsForComponentsAndPhp(): void
    {
        $tokens = $this->lexer->tokenize('text <Submit /> text');
        self::assertSame([5, 6, 13], array_map(static fn (Token $token): int => $token->position, array_slice($tokens, 1, 3)));

        $source = '<Grid><Column><Row>John</Row></Column></Grid>';
        $tokens = $this->lexer->tokenize($source);
        foreach ($tokens as $token) {
            if ($token->type !== TokenType::PHP) {
                self::assertSame($token->text, substr($source, $token->position, strlen($token->text)));
            }
        }

        $source = 'text {$name} text';
        $php = array_values(array_filter($this->lexer->tokenize($source), static fn (Token $token): bool => $token->type === TokenType::PHP))[0];
        self::assertSame(5, $php->position);
        self::assertSame('$name', $php->text);
    }

    #[DataProvider('invalidTemplateProvider')]
    public function testRejectsInvalidLexerInput(string $source, string $message): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);
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
        self::assertSame($expected, array_map(
            static fn (Token $token): array => [$token->type, $token->text],
            $this->lexer->tokenize($source),
        ));
    }
}
