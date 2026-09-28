<?php

declare(strict_types=1);

namespace EXME\Contract;

use EXME\Component\Contract\ComponentInterface;

interface ComponentRendererInterface
{
    public function render(ComponentInterface $component): string;
}