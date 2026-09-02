<?php

declare(strict_types=1);

namespace EXME\Tests\Html;

use EXME\Html\Text;
use PHPUnit\Framework\TestCase;

final class TextTest extends TestCase
{
    public function testSimpleEscapedText(): void
    {
        $text = new Text(
            '"Hello" & goodbye',
        );

        self::assertSame(
            '&quot;Hello&quot; &amp; goodbye',
            (string) $text,
        );
    }

    public function testToHtmlReturnsSameValue(): void
    {
        $text = new Text('"Hello" & goodbye');

        self::assertSame($text, $text->toHtml());
    }
}