<?php

declare(strict_types=1);

namespace EXME\Html;

final readonly class Raw implements Html
{
    public function __construct(
        public string $html,
    ) {
    }

    public function toHtml(): Html
    {
        return $this;
    }

    public function __toString(): string
    {
        return $this->html;
    }
}