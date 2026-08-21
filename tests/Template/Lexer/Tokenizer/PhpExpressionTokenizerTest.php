<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\PhpExpressionTokenizer;
use PHPUnit\Framework\TestCase;

final class PhpExpressionTokenizerTest extends TestCase
{
    private PhpExpressionTokenizer $tokenizer;

    protected function setUp(): void
    {
        $this->tokenizer = new PhpExpressionTokenizer();
    }

    public function testSupportsPhpExpression(): void
    {
        $context = new LexerContext('<?= $name ?>');

        self::assertTrue(
            $this->tokenizer->supports($context),
        );
    }

    public function testDoesNotSupportPhpBlock(): void
    {
        $context = new LexerContext('<?php $name = "Rasmus"; ?>');

        self::assertFalse(
            $this->tokenizer->supports($context),
        );
    }

    public function testTokenizesPhpExpression(): void
    {
        $source = '<?= $name ?>';
        $context = new LexerContext($source);
        $token = $this->tokenizer->tokenize($context);

        self::assertSame(TokenType::PHP_EXPRESSION, $token->type);
        self::assertSame(' $name ', $token->text);
        self::assertSame(0, $token->position);
        self::assertTrue($context->isAtEnd());
    }

    public function testTokenizesExpressionAtNonZeroPosition(): void
    {
        $source = 'prefix <?= $name ?>';

        $context = new LexerContext(
            $source,
            position: 7,
        );

        $token = $this->tokenizer->tokenize($context);

        self::assertSame(TokenType::PHP_EXPRESSION, $token->type);
        self::assertSame(' $name ', $token->text);
        self::assertSame(7, $token->position);
        self::assertTrue($context->isAtEnd());
    }
}