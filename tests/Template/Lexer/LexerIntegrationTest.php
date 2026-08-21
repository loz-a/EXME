<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
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
                [TokenType::COMPONENT_OPEN, '<'],
                [TokenType::IDENTIFIER, 'Greeting'],
                [TokenType::IDENTIFIER, 'name'],
                [TokenType::EQUALS, '='],
                [TokenType::PHP_EXPRESSION, ' $name '],
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