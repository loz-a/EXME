<?php

declare(strict_types=1);

namespace EXME\Component\Contract;

use EXME\Template\Parser\ComponentNode;

interface ComponentRendererInterface
{
    public function render(ComponentNode $node): string;
}