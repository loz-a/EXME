<?php

declare(strict_types=1);

namespace EXME\Html\Contract;

use Stringable;

interface HtmlInterface extends Stringable
{
    public function toHtml(): HtmlInterface;

    public function __toString(): string;
}