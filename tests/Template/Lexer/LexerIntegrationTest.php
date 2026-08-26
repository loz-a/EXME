<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Tokenizer\ComponentCloseTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentSelfCloseTokenizer;
use EXME\Template\Lexer\Tokenizer\ComponentTokenizer;
use EXME\Template\Lexer\Tokenizer\EqualsTokenizer;
use EXME\Template\Lexer\Tokenizer\IdentifierTokenizer;
use EXME\Template\Lexer\Tokenizer\StringTokenizer;
use EXME\Template\Lexer\Tokenizer\TokenizerChain;
use EXME\Template\Lexer\TokenType;
use PHPUnit\Framework\TestCase;

final class LexerIntegrationTest extends TestCase
{
    private Lexer $lexer;

    public function setUp(): void
    {
        $lexerFactory = new LexerFactory();
        $this->lexer = $lexerFactory->create();
    }

    public function testTokenizesCompleteTemplate(): void
    {
        $source = <<<'PHP'
            <?php
            use EXME\Component\Greeting;

            $name = 'Rasmus';
            $isAdmin = false;
            ?>

            <Greeting name="<?= $name ?>" />

            <?php if ($isAdmin): ?>
                <a href="/login">Login</a>
            <?php endif; ?>
            PHP;

        $tokens = $this->lexer->tokenize($source);

        self::assertSame(
            [
                [TokenType::PHP, "\nuse EXME\\Component\\Greeting;\n\n\$name = 'Rasmus';\n\$isAdmin = false;\n"],
                [TokenType::TEXT, "\n\n"],
                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Greeting'],
                [TokenType::IDENTIFIER, 'name'],
                [TokenType::EQUALS, '='],
                [TokenType::PHP_EXPRESSION, ' $name '],
                [TokenType::COMPONENT_SELF_CLOSE, '/>'],
                [TokenType::TEXT, "\n\n"],
                [TokenType::PHP, ' if ($isAdmin): '],
                [TokenType::TEXT, "\n    "],
                [TokenType::HTML, '<a href="/login">' ],
                [TokenType::TEXT, 'Login'],
                [TokenType::HTML, '</a>'],
                [TokenType::TEXT, "\n"],
                [TokenType::PHP, ' endif; '],
            ],
            array_map(
                static fn ($token): array => [
                    $token->type,
                    $token->text,
                ],
                $tokens,
            ),
        );
    }

    public function testComponentSelfClosingChangesLexerMode(): void
    {
        $source = '<Greeting />';

        $context = new LexerContext($source);

        $chain = new TokenizerChain([
            new ComponentTokenizer(),
            new ComponentSelfCloseTokenizer(),
            new ComponentCloseTokenizer(),
            new EqualsTokenizer(),
            new StringTokenizer(),
            new IdentifierTokenizer(),
        ]);

        $token = $chain->tokenize($context);

        self::assertSame(TokenType::COMPONENT_OPEN, $token->type);
        self::assertSame(LexerMode::COMPONENT, $context->mode);

        $token = $chain->tokenize($context);

        self::assertSame(TokenType::IDENTIFIER, $token->type);
        self::assertSame(LexerMode::COMPONENT, $context->mode);

        $token = $chain->tokenize($context);

        self::assertSame(TokenType::COMPONENT_SELF_CLOSE, $token->type);
        self::assertSame(LexerMode::TEMPLATE, $context->mode);
    }

    public function testTokenizesCompleteTemplateWithPhpExpression(): void
    {
        $source = <<<'PHP'
            <?php if ($isAdmin): ?>
                <a href="/login">Login</a>
            <?php endif; ?>

            <Greeting name="Hello <?= $name ?>!" />
            PHP;

        $tokens = $this->lexer->tokenize($source);

        self::assertSame(
            [
                [TokenType::PHP, ' if ($isAdmin): '],
                [TokenType::TEXT, "\n    "],
                [TokenType::HTML, '<a href="/login">' ],
                [TokenType::TEXT, 'Login'],
                [TokenType::HTML, '</a>'],
                [TokenType::TEXT, "\n"],
                [TokenType::PHP, ' endif; '],
                [TokenType::TEXT, "\n\n"],
                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Greeting'],
                [TokenType::IDENTIFIER, 'name'],
                [TokenType::EQUALS, '='],
                [TokenType::TEXT, "Hello "],
                [TokenType::PHP_EXPRESSION, ' $name '],
                [TokenType::TEXT, "!"],
                [TokenType::COMPONENT_SELF_CLOSE, '/>'],
            ],
            array_map(
                static fn ($token): array => [
                    $token->type,
                    $token->text,
                ],
                $tokens,
            ),
        );
    }

    public function testTokenizesHtmlWithNestedComponents(): void
    {
        $source = <<<'HTML'
            <form>
                <h1>Submit your choose:</h1>
                <Submit />
                <Cancel />
            </form>
            HTML;

        $tokens = $this->lexer->tokenize($source);

        self::assertSame(
            [
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
            ],
            array_map(
                static fn ($token): array => [
                    $token->type,
                    $token->text,
                ],
                $tokens,
            ),
        );
    }

    public function testTokenizesNestedComponents(): void
    {
        $source = <<<'HTML'
            <Grid>
                <Column>
                    <Header>Firstname:</Header>
                    <Row>John</Row>
                    <Row>Jane</Row>
                </Column>
                <Column>
                    <Header>Lastname:</Header>
                    <Row>Doe</Row>
                    <Row>Liu</Row>
                </Column>
            </Grid>
            HTML;

        $tokens = $this->lexer->tokenize($source);

        self::assertSame(
            [
                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Grid'],
                [TokenType::COMPONENT_CLOSE, '>'],

                [TokenType::TEXT, "\n    "],

                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Column'],
                [TokenType::COMPONENT_CLOSE, '>'],

                [TokenType::TEXT, "\n        "],

                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Header'],
                [TokenType::COMPONENT_CLOSE, '>'],
                [TokenType::TEXT, 'Firstname:'],
                [TokenType::COMPONENT_CLOSE, '</Header>'],

                [TokenType::TEXT, "\n        "],

                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Row'],
                [TokenType::COMPONENT_CLOSE, '>'],
                [TokenType::TEXT, 'John'],
                [TokenType::COMPONENT_CLOSE, '</Row>'],

                [TokenType::TEXT, "\n        "],

                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Row'],
                [TokenType::COMPONENT_CLOSE, '>'],
                [TokenType::TEXT, 'Jane'],
                [TokenType::COMPONENT_CLOSE, '</Row>'],

                [TokenType::TEXT, "\n    "],

                [TokenType::COMPONENT_CLOSE, '</Column>'],

                [TokenType::TEXT, "\n    "],

                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Column'],
                [TokenType::COMPONENT_CLOSE, '>'],

                [TokenType::TEXT, "\n        "],

                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Header'],
                [TokenType::COMPONENT_CLOSE, '>'],
                [TokenType::TEXT, 'Lastname:'],
                [TokenType::COMPONENT_CLOSE, '</Header>'],

                [TokenType::TEXT, "\n        "],

                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Row'],
                [TokenType::COMPONENT_CLOSE, '>'],
                [TokenType::TEXT, 'Doe'],
                [TokenType::COMPONENT_CLOSE, '</Row>'],

                [TokenType::TEXT, "\n        "],

                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Row'],
                [TokenType::COMPONENT_CLOSE, '>'],
                [TokenType::TEXT, 'Liu'],
                [TokenType::COMPONENT_CLOSE, '</Row>'],

                [TokenType::TEXT, "\n    "],

                [TokenType::COMPONENT_CLOSE, '</Column>'],

                [TokenType::TEXT, "\n"],
                [TokenType::COMPONENT_CLOSE, '</Grid>'],
            ],
            array_map(
                static fn ($token): array => [
                    $token->type,
                    $token->text,
                ],
                $tokens,
            ),
        );
    }

    public function testTokenizesSelfClosingComponentsWithWhitespaceVariations(): void
    {
        foreach (['<Submit/>', '<Submit />', '<Submit     />'] as $source) {
            self::assertSame([
                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Submit'],
                [TokenType::COMPONENT_SELF_CLOSE, '/>'],
            ], array_map(
                static fn ($token): array => [$token->type, $token->text],
                $this->lexer->tokenize($source),
            ));
        }
    }

    public function testTokenizesAttributesPhpAndComponentClosingTags(): void
    {
        $tokens = $this->lexer->tokenize('<Greeting message="Hello <?= $user->name ?>!" />');

        self::assertSame([
            [TokenType::COMPONENT_OPEN, "<"], 
            [TokenType::IDENTIFIER, "Greeting"],
            [TokenType::IDENTIFIER, "message"], 
            [TokenType::EQUALS, "="], 
            [TokenType::TEXT, "Hello "],
            [TokenType::PHP_EXPRESSION, ' $user->name '], 
            [TokenType::TEXT, "!"],
            [TokenType::COMPONENT_SELF_CLOSE, "/>"],
        ], array_map(static fn ($token): array => [$token->type, $token->text], $tokens));

        $tokens = $this->lexer->tokenize("<Header>Firstname:</Header>");
        self::assertSame([
            [TokenType::COMPONENT_OPEN, "<"], 
            [TokenType::IDENTIFIER, "Header"], 
            [TokenType::COMPONENT_CLOSE, ">"],
            [TokenType::TEXT, "Firstname:"], 
            [TokenType::COMPONENT_CLOSE, "</Header>"],
        ], array_map(static fn ($token): array => [$token->type, $token->text], $tokens));
    }

    public function testTokenizesHtmlAndPhpInsideComponents(): void
    {
        $templateText = '<Card><div class="card">Hello</div><?php if ($isAdmin): ?>Admin<?php endif; ?></Card>';
        $tokens = $this->lexer->tokenize($templateText);
        
        self::assertSame([
            [TokenType::COMPONENT_OPEN, "<"], 
            [TokenType::IDENTIFIER, "Card"], 
            [TokenType::COMPONENT_CLOSE, ">"],
            [TokenType::HTML, '<div class="card">'], 
            [TokenType::TEXT, "Hello"], 
            [TokenType::HTML, "</div>"],
            [TokenType::PHP, ' if ($isAdmin): '], 
            [TokenType::TEXT, "Admin"], 
            [TokenType::PHP, ' endif; '],
            [TokenType::COMPONENT_CLOSE, "</Card>"],
        ], array_map(static fn ($token): array => [$token->type, $token->text], $tokens));
    }

    public function testUsesAbsolutePositionsForNestedComponentTokens(): void
    {
        $tokens = $this->lexer->tokenize("text <Submit /> text");
        self::assertSame([5, 6, 13], array_map(static fn ($token): int => $token->position, array_slice($tokens, 1, 3)));

        $tokens = $this->lexer->tokenize("<Grid>
    <Column>
        <Row>John</Row>
    </Column>
</Grid>");
        self::assertSame([0, 1, 5, 6, 11, 12, 18, 19, 28, 29, 32, 33, 37, 43, 48, 57, 58], array_map(
            static fn ($token): int => $token->position,
            $tokens,
        ));
    }

    public function testTokenizesMultilineComponentAttributes(): void
    {
        $source = "<Greeting\n    name=\"John\"\n    age=\"15\"\n    role=\"admin\"\n/>";
        $tokens = $this->lexer->tokenize($source);
        self::assertSame([
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
        ], array_map(
            static fn ($token): array => [$token->type, $token->text],
            $tokens,
        ));
    }
}