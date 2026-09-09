<?php

declare(strict_types=1);

namespace EXME\Template\Parser;

use EXME\Html\Contract\HtmlFactoryInterface;
use EXME\Html\Contract\HtmlInterface;
use EXME\Html\Fragment;
use EXME\Template\Parser\Contract\ParserContextInterface;
use SplStack;

final class HtmlResultBuilder
{
    private SplStack $scope;

    private int $depth;

    public function __construct(
        private HtmlFactoryInterface $htmlFactory,
    ){
    }

    public function build(ParserContextInterface $ctx): HtmlInterface
    {
        $htmlResult = [];

        foreach ($ctx as $token) {
            $htmlResult[] = $this->htmlFactory->create($token);
        }

        return count($htmlResult) > 1 ? new Fragment(... $htmlResult) : $htmlResult[0];
    }
}