<?php

declare(strict_types=1);

namespace EXME\Html\Factory;

use EXME\Html\Contract\HtmlFactoryInterface;
use EXME\Html\Contract\HtmlInterface;
use EXME\Html\Raw;
use EXME\Html\Text;
use EXME\Template\Lexer\Token;
use EXME\Template\Lexer\TokenType;

final class HtmlFactory implements HtmlFactoryInterface
{
    public function create(Token $token): HtmlInterface
    {
        return $this($token);
    }

    public function __invoke(Token $token): HtmlInterface
    {
        return match ($token->type) {
            TokenType::TEXT => new Text($token->text),
            TokenType::HTML || TokenType::PHP => new Raw($token->text),
            // TokenType::
        };
    }
}