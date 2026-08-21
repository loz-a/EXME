<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Tokenizer\ComponentSelfCloseTokenizer;
use PHPUnit\Framework\TestCase;

final class ComponentSelfCloseTokenizerTest extends TestCase
{
    public function testSwitchesModeToTemplate(): void
{
    $context = new LexerContext(
        '/>',
        mode: LexerMode::COMPONENT,
    );

    $tokenizer = new ComponentSelfCloseTokenizer();

    $tokenizer->tokenize($context);

    self::assertSame(
        LexerMode::TEMPLATE,
        $context->mode,
    );
}
}