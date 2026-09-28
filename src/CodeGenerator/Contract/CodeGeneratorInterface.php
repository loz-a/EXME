<?php

declare(strict_types=1);

namespace EXME\ClassGenerator\Contract;

interface CodeGeneratorInterface
{
    public function generate(string $className): void;
}