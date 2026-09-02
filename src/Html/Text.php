<?php

declare(strict_types=1);

namespace EXME\Html;

final readonly class Text implements Html
{
    public function __construct(
        public string $text,
    ){
    }

    public function toHtml(): Html
    {
        return $this;
    }

    public function __toString(): string
    {
        return htmlspecialchars($this->text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
    }
}