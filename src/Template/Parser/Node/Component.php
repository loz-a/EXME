<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Node;

use EXME\Template\Parser\Node\Contract\NodeInterface;

use function sprintf;
use function var_export;

final readonly class Component implements NodeInterface
{
    use NodeTrait;

    private const RENDER_TEMPLATE = <<<'EOT'
        render_component(
            %1$s::class,
            %2$s,
            %3$s
        );
    EOT;

    public function __construct(
        public readonly string $name,
        public readonly array $attributes,
        public readonly ?NodeInterface $slot = null,
    ){
    }

    public function hasAttributes(): bool
    {
        return count($this->attributes) !== 0;
    }

    public function render(): string
    {
        return sprintf(
            self::RENDER_TEMPLATE,
            $this->name,
            var_export($this->attributes, true),
            $this->slot?->render() ?? '',
        );
    }
}