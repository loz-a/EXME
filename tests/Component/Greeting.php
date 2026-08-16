<?php

declare(strict_types=1);

namespace EXME\Tests\Component;

final class Greeting
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
    ) {
    }

    public function render(): string
    {
        return <<<HTML
            <h1>Hello {$this->name}!</h1>
            <p>You are a {$this->type}</p>
        HTML;        
    }
}