<?php

declare(strict_types=1);

namespace EXME\Tests\Html;

use EXME\Html\Element;
use EXME\Html\Fragment;
use EXME\Html\Text;
use PHPUnit\Framework\TestCase;

final class FragmentTest extends TestCase
{
    public function testRendersMultipleChildren(): void
    {
        $fragment = new Fragment(
            new Element('h1', [], ['Hello']),
            new Element('p', [], ['World']),
        );

        self::assertSame(
            '<h1>Hello</h1><p>World</p>',
            (string) $fragment,
        );
    }

    public function testToHtmlReturnsSameValue(): void
    {
        $fragment = new Fragment();

        self::assertSame($fragment, $fragment->toHtml());
    }

    public function testEscapesScalarChildren(): void
    {
        $fragment = new Fragment(
            new Text('<strong>unsafe</strong>'),
        );

        self::assertSame(
            '&lt;strong&gt;unsafe&lt;/strong&gt;',
            (string) $fragment,
        );
    }

    public function testRendersNestedFragments(): void
    {
        $fragment = new Fragment(
            new Fragment(
                new Element(tag: 'span', children: ['Hello']),
            ),
        );

        self::assertSame(
            '<span>Hello</span>',
            (string) $fragment,
        );
    }
}