<?php

declare(strict_types=1);

namespace EXME\Html;

use EXME\Html\Contract\HtmlInterface;

final readonly class Text implements HtmlInterface
{
    public function __construct(
        public string $text,
    ){
    }

    public function toHtml(): HtmlInterface
    {
        return $this;
    }

    public function __toString(): string
    {
        return htmlspecialchars($this->text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
    }
}