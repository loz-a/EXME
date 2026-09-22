<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Tokenizer\ComponentStartTokenizer;
use PHPUnit\Framework\TestCase;

final class ComponentStartTokenizerTest extends TestCase
{
    public function testSwitchesModeToComponent(): void
    {
        $context = new LexerContext(
            '<Greeting',
            mode: LexerMode::TEMPLATE,
        );

        $tokenizer = new ComponentStartTokenizer();

        $tokenizer->tokenize($context);

        self::assertSame(LexerMode::COMPONENT, $context->mode);
    }
}