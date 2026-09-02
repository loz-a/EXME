<?php

declare(strict_types=1);

namespace EXME\Tests\Html;

use EXME\Html\Raw;
use PHPUnit\Framework\TestCase;

final class RawTest extends TestCase
{
    public function testPreservesHtmlVerbatim(): void
    {
        $raw = new Raw(
            '<script>alert("trusted")</script>',
        );

        self::assertSame(
            '<script>alert("trusted")</script>',
            (string) $raw,
        );
    }

    public function testToHtmlReturnsSameValue(): void
    {
        $raw = new Raw('<strong>Hello</strong>');

        self::assertSame($raw, $raw->toHtml());
    }
}