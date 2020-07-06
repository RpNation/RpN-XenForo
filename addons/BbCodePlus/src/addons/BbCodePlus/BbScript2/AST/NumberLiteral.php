<?php

namespace BbCodePlus\BbScript2\AST;

class NumberLiteral extends ASTNode
{
    private $value;

    public function __construct($startIdx, $endIdx, $value)
    {
        parent::__construct($startIdx, $endIdx);
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}