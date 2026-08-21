<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\TokenType;
use EXME\Template\Lexer\Tokenizer\PhpTokenizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhpTokenizerTest extends TestCase
{
    private PhpTokenizer $tokenizer;

    protected function setUp(): void
    {
        $this->tokenizer = new PhpTokenizer();
    }

    #[DataProvider('supportedPhpProvider')]
    public function testSupportsPhpBlock(string $source): void
    {
        $context = new LexerContext($source);

        self::assertTrue(
            $this->tokenizer->supports($context),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function supportedPhpProvider(): iterable
    {
        yield 'php block' => [
            '<?php $name = "Rasmus"; ?>',
        ];

        yield 'php block with whitespace' => [
            '<?php
                $name = "Rasmus";
            ?>',
        ];
    }

    public function testDoesNotSupportPhpExpression(): void
    {
        $context = new LexerContext('<?= $name ?>');

        self::assertFalse(
            $this->tokenizer->supports($context),
        );
    }

    public function testTokenizesPhpBlock(): void
    {
        $source = '<?php $name = "Rasmus"; ?>';

        $context = new LexerContext($source);

        $token = $this->tokenizer->tokenize($context);

        self::assertSame(
            TokenType::PHP,
            $token->type,
        );

        self::assertSame(
            ' $name = "Rasmus"; ',
            $token->text,
        );

        self::assertSame(0, $token->position);

        self::assertTrue($context->isAtEnd());
    }

    public function testTokenizesPhpBlockWithoutClosingTag(): void
    {
        $source = '<?php $name = "Rasmus";';

        $context = new LexerContext($source);

        $token = $this->tokenizer->tokenize($context);

        self::assertSame(TokenType::PHP, $token->type);
        self::assertSame(' $name = "Rasmus";', $token->text);
        self::assertSame(0, $token->position);
        self::assertTrue($context->isAtEnd());
    }
}