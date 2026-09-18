<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node\Contract;

use Stringable;

interface NodeInterface extends Stringable
{
    public function render(): string;

    public function __toString(): string;
}