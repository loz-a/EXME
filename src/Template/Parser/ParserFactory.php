<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

use EXME\Html\Factory\HtmlFactory;
use EXME\Template\Parser\Contract\ParserInterface;
use EXME\Template\Parser\TokenValidator\ComponentTokenValidator;
use EXME\Template\Parser\TokenValidator\HtmlTokenValidator;
use EXME\Template\Parser\TokenValidator\PhpTokenValidator;
use EXME\Template\Parser\TokenValidator\TextTokenValidator;

final class ParserFactory
{
    public function create(): ParserInterface
    {
        $tokenValidators = [
            new ComponentTokenValidator(),
            new PhpTokenValidator(),
            new HtmlTokenValidator(),
            new TextTokenValidator(),
        ];

        return new Parser(
            tokenValidator: new TokenValidator(...$tokenValidators),
            resultBuilder: new HtmlResultBuilder(new HtmlFactory()),
        );
    }
}