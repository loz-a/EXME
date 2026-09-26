<?php

declare(strict_types=1);

namespace EXMETests\Template\Parser;

use EXME\Template\Lexer\Lexer;
use EXME\Template\Lexer\LexerFactory;
use EXME\Template\Parser\Node\Attribute\AttributeFactory;
use EXME\Template\Parser\Node\Attribute\Contract\AttributeInterface;
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
            nodeFactory: new NodeFactory(new AttributeFactory()),
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

    public function testParsesComponentWithTextAttributes(): void
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

    public function testParsesComponentWithPhpAttributes(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<Greeting name={$name} type={$type} />',
            );

        $node = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $node);
        self::assertSame('Greeting', $node->name);
        self::assertCount(2, $node->attributes);
    }

    public function testParsesComponentWithMixedAttributes(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<Greeting name="Rasmus" type={$type} />',
            );

        $node = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $node);
        self::assertSame('Greeting', $node->name);
        self::assertCount(2, $node->attributes);
    }

    public function testParsesComponentWithNumAttribute(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<Greeting age=15 />',
            );

        $node = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $node);
        self::assertSame('Greeting', $node->name);
        self::assertCount(1, $node->attributes);
        self::assertArrayHasKey('age', $node->attributes);
        self::assertInstanceOf(AttributeInterface::class, $node->attributes['age']);
        self::assertEquals('\'age\' => 15', (string) $node->attributes['age']);
    }

    public function testParsesComponentWithNegativeFloatAttribute(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<Greeting age=-15.05 />',
            );

        $node = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $node);
        self::assertSame('Greeting', $node->name);
        self::assertCount(1, $node->attributes);
        self::assertArrayHasKey('age', $node->attributes);
        self::assertInstanceOf(AttributeInterface::class, $node->attributes['age']);
        self::assertEquals('\'age\' => -15.05', (string) $node->attributes['age']);
    }

    public function testParsesComponentWithBoolFalseAttribute(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<User isAdmin=false />',
            );

        $node = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $node);
        self::assertSame('User', $node->name);
        self::assertCount(1, $node->attributes);
        self::assertArrayHasKey('isAdmin', $node->attributes);
        self::assertInstanceOf(AttributeInterface::class, $node->attributes['isAdmin']);
        self::assertEquals('\'isAdmin\' => false', (string) $node->attributes['isAdmin']);
    }

    public function testParsesComponentWithVeryMixedAttributes(): void
    {
        $tokens = $this
            ->lexer
            ->tokenize(
                '<Greeting name="Rasmus" age={$age} type="Type {$type}" negative-number=-22.222 isFoo=true />',
            );

        $node = $this->parser->parse($tokens);

        self::assertInstanceOf(Component::class, $node);
        self::assertSame('Greeting', $node->name);
        self::assertCount(5, $node->attributes);
        self::assertEquals('\'name\' => \'Rasmus\'', (string) $node->attributes['name']);
        self::assertEquals('\'age\' => $age', (string) $node->attributes['age']);
        self::assertEquals('\'type\' => \'Type \' . $type', (string) $node->attributes['type']);
        self::assertEquals('\'negative-number\' => -22.222', (string) $node->attributes['negative-number']);
        self::assertEquals('\'isFoo\' => true', (string) $node->attributes['isFoo']);
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