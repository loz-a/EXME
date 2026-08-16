<?php

declare(strict_types=1);

namespace EXME\Tests\Template\Parser;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Parser\ComponentNode;
use EXME\Template\Parser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testParsesComponentWithoutAttributes(): void
    {
        $tokens = (new Lexer('<Greeting />'))->tokenize();

        $node = (new Parser($tokens))->parse();

        self::assertEquals(
            new ComponentNode(
                name: 'Greeting',
                attributes: [],
            ),
            $node,
        );
    }

    public function testParsesComponentWithAttributes(): void
    {
        $tokens = (new Lexer(
            '<Greeting name="Rasmus" type="guest" />',
        ))->tokenize();

        $node = (new Parser($tokens))->parse();
 
        self::assertEquals(
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
        $tokens = (new Lexer(
            '<Greeting name="Rasmus" = />',
        ))->tokenize();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected token "identifier", got "equals" at position 24');

        (new Parser($tokens))->parse();
    }

    public function testRejectsDuplicateAttributes(): void
    {
        $tokens = (new Lexer('<Greeting type="guest" type="admin" />'))->tokenize();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Duplicate attribute "type" at position 23');

        (new Parser($tokens))->parse();
    }

    public function testCanParseTheSameTokensMoreThanOnce(): void
    {
        $parser = new Parser((new Lexer('<Greeting name="Rasmus" />'))->tokenize());

        self::assertEquals($parser->parse(), $parser->parse());
    }

    #[DataProvider('invalidTemplateProvider')]
    public function testRejectsInvalidTemplates(string $template, string $message): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        (new Parser((new Lexer($template))->tokenize()))->parse();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidTemplateProvider(): iterable
    {
        yield 'empty input' => [
            '',
            'Expected token "tag_open", but reached end of input',
        ];

        yield 'missing component name' => [
            '< />',
            'Expected token "identifier", got "tag_self_close" at position 2',
        ];

        yield 'missing attribute equals sign' => [
            '<Greeting name "Rasmus" />',
            'Expected token "equals", got "string" at position 15',
        ];

        yield 'missing attribute value' => [
            '<Greeting name= />',
            'Expected token "string", got "tag_self_close" at position 16',
        ];

        yield 'missing self-closing tag' => [
            '<Greeting',
            'Expected token "tag_self_close", but reached end of input',
        ];

        yield 'unexpected tokens after component' => [
            '<Greeting /><Farewell />',
            'Unexpected token "<" at position 12',
        ];
    }
}
