<?php

declare(strict_types=1);

namespace EXME\Html;

use Stringable;

interface Html extends Stringable
{
    public function toHtml(): Html;

    public function __toString(): string;
}