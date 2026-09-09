<?php

declare(strict_types=1);

namespace EXME\Html;

use EXME\Html\Contract\HtmlInterface;

final readonly class Fragment implements HtmlInterface
{
    public readonly array $children;
    /**
     * @param array<int, HtmlInterface> $children
     */
    public function __construct(HtmlInterface ...$children)
    {
        $this->children = $children;
    }

    public function toHtml(): HtmlInterface
    {
        return $this;
    }

    public function __toString(): string
    {
        $html = [];

        foreach ($this->children as $child) {
            $html[] = (string) $child;
        }

        return implode('', $html);
    }
}