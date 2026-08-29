<?php

declare(strict_types=1);

namespace EXME\Template\Lexer;

/**
 * Lexical token types produced by the EXME Lexer.
 *
 * The Lexer is responsible only for recognizing lexical elements.
 * It does not determine the semantic structure of a template.
 *
 * Component tokens:
 * - COMPONENT_OPEN
 * - COMPONENT_CLOSE
 * - COMPONENT_SELF_CLOSE
 *
 * Component syntax tokens:
 * - IDENTIFIER
 * - EQUALS
 *
 * Content tokens:
 * - TEXT
 * - HTML
 *
 * PHP tokens:
 * - PHP
 */

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
}