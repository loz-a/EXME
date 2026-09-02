<?php

declare(strict_types=1);

namespace EXME\Html;

final readonly class Fragment implements Html
{
    public readonly array $children;
    /**
     * @param array<int, Html> $children
     */
    public function __construct(Html ...$children)
    {
        $this->children = $children;
    }

    public function toHtml(): Html
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