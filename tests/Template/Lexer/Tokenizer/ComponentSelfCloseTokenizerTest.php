<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Lexer\Tokenizer;

use EXME\Template\Lexer\LexerContext;
use EXME\Template\Lexer\LexerMode;
use EXME\Template\Lexer\Tokenizer\Component\SelfCloseTokenizer;
use PHPUnit\Framework\TestCase;

final class ComponentSelfCloseTokenizerTest extends TestCase
{
    public function testSwitchesModeToTemplate(): void
{
    $context = new LexerContext(
        '/>',
        mode: LexerMode::COMPONENT,
    );

    $tokenizer = new SelfCloseTokenizer();

    $tokenizer->tokenize($context);

    self::assertSame(
        LexerMode::TEMPLATE,
        $context->mode,
    );
}
}