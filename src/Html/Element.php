<?php

declare(strict_types=1);

namespace EXME\Html;

use Stringable;
use TypeError;
use ValueError;

use function sprintf;
use function implode;
use function is_scalar;
use function get_debug_type;
use function htmlspecialchars;
use function strtolower;

final readonly class Element implements Html
{
    public array $children;

    /**
     * @param array<string, mixed> $attributes
     * @param array<int, mixed> $children
     */
    public function __construct(
        public string $tag,
        public array $attributes = [],
        array $children = [],
    ) {
        $this->validateTagName($tag);

        foreach ($attributes as $name => $value) {
            $this->validateAttribute($name, $value);
        }

        $this->setChildren(...$children);
    }

    public function toHtml(): Html
    {
        return $this;
    }

    public function __toString(): string
    {
        return $this->render();
    }
        
    private function setChildren(Html|string ...$rawChildren): void
    {
        $children = [];
        foreach ($rawChildren as $child) {            
            $children[] = is_string($child) ? new Text($child) : $child;
        }
        $this->children = $children;
    }

    private function render(): string
    {
        $attributes = [];

        foreach ($this->attributes as $name => $value) {
            $attribute = $this->renderAttribute($name, $value);

            if ($attribute !== '') {
                $attributes[] = $attribute;
            }
        }

        if ($this->isSelfCloseElement($this->tag)) {
            if (!count($attributes)) {
                return sprintf('<%s />', $this->tag);
            }
            return sprintf('<%s %s />', $this->tag, implode(' ', $attributes));
        }

        $children = [];
        foreach ($this->children as $child) {
            $children[] = (string) $child;
        }

        if (count($attributes)) {
            return sprintf('<%1$s %2$s>%3$s</%1$s>', 
                $this->tag, implode(' ', $attributes), implode(PHP_EOL, $children));
        }

        return sprintf('<%1$s>%2$s</%1$s>', $this->tag, implode(PHP_EOL, $children));
    }

    private function renderAttribute(string $name, mixed $value): string 
    {
        if ($value === null || $value === false) {
            return '';
        }

        if ($value === true) {
            return $name;
        }        

        return sprintf(
            '%s="%s"', 
            $name, 
            htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)
        );
    }

    private function validateTagName(string $name): void
    {
        if (
            $name === ''
            || !preg_match('/^[A-Za-z][A-Za-z0-9:-]*$/', $name)
        ) {
            throw new ValueError(sprintf('Invalid HTML tag name "%s"', $name));
        }
    }

    private function validateAttribute(string $name, mixed $value): void
    {
        if ($name === ''
            || !preg_match('/^[^\x00\t\n\f\r "\'\/=>]+$/', $name)
        ) {
            throw new ValueError(sprintf('Invalid HTML attribute name "%s"', $name));
        }

        $isValid = null === $value || is_scalar($value) || $value instanceof Stringable;
        if (!$isValid) {
            throw new TypeError(
                sprintf('Unsupported value for attribute "%s": %s', $name, get_debug_type($value)));
        }
    }

    private function isSelfCloseElement(string $tag): bool
    {
        return match (strtolower($tag)) {
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr' => true,
            default => false,
        };
    }
}