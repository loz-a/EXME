<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node;

trait NodeTrait
{
    abstract public function render(): string;

    public function __toString(): string
    {
        return $this->render();
    }
}