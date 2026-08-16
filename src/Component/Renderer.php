<?php

declare(strict_types=1);

namespace EXME\Component;

use EXME\Component\Contract\ComponentRendererInterface;
use EXME\Template\Parser\ComponentNode;

final class Renderer implements ComponentRendererInterface
{
    public function render(ComponentNode $node): string
    {
        $class = $node->name;

        if (!class_exists($class)) {
            throw new \RuntimeException(
                sprintf(
                    'Component class "%s" does not exist',
                    $class,
                ),
            );
        }

        $component = new $class(...$node->attributes);

        if (!method_exists($component, 'render')) {
            throw new \RuntimeException(
                sprintf(
                    'Component class "%s" does not have a render() method',
                    $class,
                ),
            );
        }

        $result = $component->render();

        if (!is_string($result)) {
            throw new \RuntimeException(
                sprintf(
                    'Component class "%s" render() must return a string',
                    $class,
                ),
            );
        }

        return $result;
    }
}