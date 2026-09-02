<?php

declare(strict_types=1);

namespace EXME\Tests\Html;

use EXME\Html\Element;
use EXME\Html\Raw;
use EXME\Html\Text;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ElementTest extends TestCase
{
    public function testCreatesElement(): void
    {
        $element = new Element(
            tag: 'button',
            attributes: ['class' => 'btn'],
            children: ['Sign in'],
        );

        self::assertSame('button', $element->tag);
        self::assertSame(['class' => 'btn'], $element->attributes);
        self::assertSame(Text::class, $element->children[0]::class);
        self::assertSame('Sign in', $element->children[0]->text);
    }

    public function testRendersElement(): void
    {
        $element = new Element(
            tag: 'button',
            attributes: ['class' => 'btn'],
            children: ['Sign in'],
        );

        self::assertSame(
            '<button class="btn">Sign in</button>',
            (string) $element,
        );
    }

    public function testToHtmlReturnsSameValue(): void
    {
        $element = new Element('div');

        self::assertSame($element, $element->toHtml());
    }

    public function testEscapesTextChildren(): void
    {
        $element = new Element(
            tag: 'div',
            children: [
                new Text('<script>alert("xss")</script>')
            ],
        );

        self::assertSame(
            '<div>&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;</div>',
            (string) $element,
        );
    }

    public function testEscapesAttributeValues(): void
    {
        $element = new Element(
            tag: 'div',
            attributes: [
                'title' => '"Hello" & goodbye'
            ]
        );

        self::assertSame(
            '<div title="&quot;Hello&quot; &amp; goodbye"></div>',
            (string) $element,
        );
    }

    public function testRendersBooleanAttribute(): void
    {
        $element = new Element(
            tag: 'input',
            attributes: [
                'disabled' => true
            ]
        );

        self::assertSame(
            '<input disabled />',
            (string) $element,
        );
    }

    public function testOmitsNullAndFalseAttributes(): void
    {
        $element = new Element(
            tag: 'input',
            attributes: [
                'disabled' => false,
                'required' => null,
            ]
        );

        self::assertSame(
            '<input />',
            (string) $element,
        );
    }

    public function testRendersVoidElementWithoutClosingTag(): void
    {
        self::assertSame(
            '<br />',
            (string) new Element('br'),
        );
    }

    public function testRendersNestedHtml(): void
    {
        $element = new Element(
            tag: 'div',
            children: [
                new Element(
                    tag: 'span',
                    children: [
                        new Text('Hello'),
                    ],
                ),
            ],
        );

        self::assertSame(
            '<div><span>Hello</span></div>',
            (string) $element,
        );
    }

    public function testRendersRawHtmlWithoutEscaping(): void
    {
        $element = new Element(
            tag: 'div',
            attributes: [],
            children: [
                new Raw('<strong>Trusted</strong>'),
            ],
        );

        self::assertSame(
            '<div><strong>Trusted</strong></div>',
            (string) $element,
        );
    }

    public function testRejectsInvalidTagName(): void
    {
        $this->expectException(\ValueError::class);

        new Element(
            '<script',
        );
    }

    #[DataProvider('unsuportedAttributeNameProvider')]
    public function testRejectsInvalidAttributeName(string $attributeName): void
    {
        $this->expectException(\ValueError::class);

        new Element(
            tag: 'div',
            attributes: [
                $attributeName => 'value',
            ],
        );
    }

    public static function unsuportedAttributeNameProvider(): iterable
    {
        yield 'no whitespace' => ['data value'];
        yield 'no equals' => ['data=value'];
        yield 'no slash' => ['data/foo'];
        yield 'no great than' => ['data>foo'];
        yield 'no double quote' => ['data"foo'];
        yield 'no quote' => ["data'foo"];
    }

    #[DataProvider('supportedAttributeNameProvider')]
    public function testAcceptsSupportedAttributeName(string $attributeName): void
    {
        $element = new Element(
            tag: 'div',
            attributes: [
                $attributeName => 'value',
            ],
        );

        self::assertSame('value', $element->attributes[$attributeName]);
    }

    public static function supportedAttributeNameProvider(): iterable
    {
        yield 'letters' => ['id'];
        yield 'uppercase letters' => ['ID'];
        yield 'hyphen' => ['data-value'];
        yield 'underscore' => ['data_value'];
        yield 'dot' => ['data.value'];
        yield 'colon' => ['data:value'];
        yield 'digits' => ['data123'];
        yield 'starts with underscore' => ['_data'];
        yield 'starts with colon' => [':data'];
        yield 'mixed' => ['data-foo_123.bar:baz'];
    }

    public function testRejectsUnsupportedAttributeValue(): void
    {
        $this->expectException(\TypeError::class);

        new Element(
            tag: 'div',
            attributes: [
                'data-value' => [],
            ],
        );
    }

    public function testRejectsUnsupportedChild(): void
    {
        $this->expectException(\TypeError::class);

        new Element(
            tag: 'div',
            children: [
                new \stdClass(),
            ],
        );
    }
}