<?php

namespace BbCodePlus\BbScript2\AST;

use Exception;

class ASTError extends Exception
{
    private $node;

    public function __construct($node, $message)
    {
        parent::__construct($message);
        $this->node = $node;
    }

    public function format($text)
    {
        if ($this->node)
        {
            $details = $this->node->findInText($text);
            return $this->message . ': ' . $details['text']
                . ' (line ' . $details['line']
                . ', column ' . $details['column']
                . ')';
        }
        else
        {
            return $this->message;
        }
    }
}