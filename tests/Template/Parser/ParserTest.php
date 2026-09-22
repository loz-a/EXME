<?php

declare(strict_types=1);

namespace EXMETests\Template\Parser;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Parser\Node\Component;
use EXME\Template\Parser\Node\Factory\NodeFactory;
use EXME\Template\Parser\Node\Fragment;
use EXME\Template\Parser\Node\Text;
use EXME\Template\Parser\Parser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ParserTest extends TestCase
{
    private Lexer $lexer;
    private Parser $parser;

    protected function setUp(): void
    {
        $this->lexer = new LexerFactory()->create();

        $this->parser = new Parser(
            nodeFactory: new NodeFactory(),
        );
    }

    public function testParsesSelfClosingComponent(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<Greeting />',
            );

        $node = $this->parser->parse($tokens);

        self::assertEquals(
            new Component(
                name: 'Greeting',
                attributes: [],
            ),
            $node,
        );        
    }

    public function testHasAttributes(): void
    {
        $componentWithNoAttributes = new Component(
            name: 'Greeting',
            attributes: [],
        );
        self::assertFalse($componentWithNoAttributes->hasAttributes());

        $componentWithAttributes = new Component(
            name: 'Greeting',
            attributes: [
                'bar' => 'boo',
                'hello' => 'world',
            ],
        );

        self::assertTrue($componentWithAttributes->hasAttributes());
        self::assertCount(2, $componentWithAttributes->attributes);
    }

    public function testParsesComponentWithAttributes(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<Greeting name="Rasmus" type="guest" />',
            );

        $node = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $node);

        self::assertSame('Greeting', $node->name);

        self::assertCount(2, $node->attributes);
    }

    public function testParsesComponentWithTextSlot(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<Greeting>Hello</Greeting>',
            );

        $node = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $node);

        self::assertInstanceOf(Fragment::class, $node->slot);

        self::assertEquals(new Text('Hello'), $node->slot->children[0]);
    }

    public function testParsesNestedComponents(): void
    {
        $tokens = $this->lexer->tokenize(
            <<<'HTML'
            <User>
                <Profile>
                    John
                </Profile>
            </User>
            HTML,
        );

        $user = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $user);

        self::assertSame('User', $user->name);

        self::assertInstanceOf(Fragment::class, $user->slot);

        /*
         * Whitespace is represented by Text nodes.
         *
         * The important thing here is that Profile was parsed
         * as a child of User rather than as a sibling.
         */
        $profile = null;

        foreach ($user->slot->children as $child) {
            $isProfileComponent = $child instanceof Component && $child->name === 'Profile';
            if ($isProfileComponent) {
                $profile = $child;
                break;
            }
        }

        self::assertNotNull($profile);
    }

    public function testRejectsMismatchedClosingTag(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<User>Hello</Admin>',
            );

        $this->expectException(RuntimeException::class);
        $this
            ->expectExceptionMessageIsOrContains(
                'Expected closing component </User>, got </Admin>',
            );

        $this->parser->parse($tokens);
    }

    public function testRejectsUnexpectedClosingTag(): void
    {
        $this->expectException(RuntimeException::class);        
        $this->expectExceptionMessageIsOrContains('Unexpected closing component </User> at position 0');

        $tokens = $this->lexer->tokenize('</User>');
        $this->parser->parse($tokens);        
    }

    public function testRejectsUnclosedComponent(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Expected closing tag for <User>');

        $tokens = $this->lexer->tokenize('<User>Hello');
        $this->parser->parse($tokens);        
    }

    public function testRejectsIncorrectlyNestedComponents(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Expected closing component </Profile>, got </User>');

        $tokens = $this->lexer->tokenize('<User><Profile></User></Profile>');
        $this->parser->parse($tokens);        
    }
}