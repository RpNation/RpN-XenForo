<?php

namespace BbCodePlus\BbScript2\AST;

class QuotedString extends ASTNode
{
    private $string;

    public function __construct($startIdx, $endIdx, $string)
    {
        parent::__construct($startIdx, $endIdx);
        $this->string = $string;
    }

    public function getString()
    {
        return $this->string;
    }
}