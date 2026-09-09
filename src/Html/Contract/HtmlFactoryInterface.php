<?php

declare(strict_types=1);

namespace EXME\Html\Contract;

use EXME\Template\Lexer\Token;

interface HtmlFactoryInterface
{
    public function create(Token $token): HtmlInterface;

    public function __invoke(Token $token): HtmlInterface;
}