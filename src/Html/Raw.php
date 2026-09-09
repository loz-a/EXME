<?php

declare(strict_types=1);

namespace EXME\Html;

use EXME\Html\Contract\HtmlInterface;

final readonly class Raw implements HtmlInterface
{
    public function __construct(
        public string $html,
    ) {
    }

    public function toHtml(): HtmlInterface
    {
        return $this;
    }

    public function __toString(): string
    {
        return $this->html;
    }
}