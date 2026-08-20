<?php

declare(strict_types=1);

namespace EXMETests\Template\Parser;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Parser\ComponentNode;
use EXME\Template\Parser\Parser;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Parser is not finished yet.');
    }

    public function testParsesComponentWithoutAttributes(): void
    {
        $tokens = (new Lexer())->tokenize('<Greeting />');

        $node = (new Parser($tokens))->parse();

        self::assertSame(
            new ComponentNode(
                name: 'Greeting',
                attributes: [],
            ),
            $node,
        );
    }

    public function testParsesComponentWithAttributes(): void
    {
        $tokens = (new Lexer())->tokenize(
            '<Greeting name="Rasmus" type="guest" />',
        );

        $node = (new Parser($tokens))->parse();

        self::assertSame(
            new ComponentNode(
                name: 'Greeting',
                attributes: [
                    'name' => 'Rasmus',
                    'type' => 'guest',
                ],
            ),
            $node,
        );
    }

    public function testRejectsUnexpectedToken(): void
    {
        $tokens = (new Lexer())->tokenize(
            '<Greeting name="Rasmus" = />',
        );

        $this->expectException(\RuntimeException::class);

        (new Parser($tokens))->parse();
    }
}
