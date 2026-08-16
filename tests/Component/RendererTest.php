<?php

declare(strict_types=1);

namespace EXME\Tests\Component;

use EXME\Component\Renderer;
use EXME\Template\Parser\ComponentNode;
use PHPUnit\Framework\TestCase;

final class ComponentRendererTest extends TestCase
{
    public function testRendersComponent(): void
    {
        $node = new ComponentNode(
            name: Greeting::class,
            attributes: [
                'name' => 'Rasmus',
                'type' => 'guest',
            ],
        );

        $renderer = new Renderer();

        self::assertSame(
            "    <h1>Hello Rasmus!</h1>\n    <p>You are a guest</p>",
            $renderer->render($node),
        );
    }

    public function testRejectsUnknownComponent(): void
    {
        $node = new ComponentNode(
            name: 'UnknownComponent',
            attributes: [],
        );

        $renderer = new Renderer();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Component class "UnknownComponent" does not exist',
        );

        $renderer->render($node);
    }

    public function testRejectsComponentWithoutRenderMethod(): void
    {
        $node = new ComponentNode(
            name: \stdClass::class,
            attributes: [],
        );

        $renderer = new Renderer();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Component class "stdClass" does not have a render() method',
        );

        $renderer->render($node);
    }
}
