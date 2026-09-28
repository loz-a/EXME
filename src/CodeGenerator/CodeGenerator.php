<?php

declare(strict_types=1);

namespace EXME\CodeGenerator;

use EXME\ClassGenerator\Contract\CodeGeneratorInterface;
use Override;

final class CodeGenerator implements CodeGeneratorInterface
{
    public function __construct()
    {
        
    }

    #[Override]
    public function generate(string $className): void
    {
        throw new \Exception('Not implemented');
    }
}