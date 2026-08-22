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
}