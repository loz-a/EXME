<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

enum TokenType: string
{
    case COMPONENT_OPEN = 'component_open';
    case COMPONENT_CLOSE = 'component_close';
    case COMPONENT_SELF_CLOSE = 'component_self_close';
    
    case IDENTIFIER = 'identifier';
    case EQUALS = 'equals';
    case TEXT = 'text';
    
    case HTML = 'html';
    case PHP = 'php';
    case PHP_EXPRESSION = 'php_expression';
}