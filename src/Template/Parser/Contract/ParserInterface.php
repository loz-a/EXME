<?php

declare(strict_types=1);

namespace EXME\Template\Parser\Contract;

use EXME\Html\Contract\HtmlInterface;

interface ParserInterface 
{
    public function parse(ParserContextInterface $ctx): HtmlInterface;
}

