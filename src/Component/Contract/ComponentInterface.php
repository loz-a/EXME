<?php

declare(strict_types=1);

namespace EXME\Component\Contract;

interface ComponentInterface
{
    public function render(): string;
}