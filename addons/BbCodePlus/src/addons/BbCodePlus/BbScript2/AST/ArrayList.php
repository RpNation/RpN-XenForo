<?php

namespace BbCodePlus\BbScript2\AST;

class ArrayList extends ASTNode
{
    private $elements;

    public function __construct($startIdx, $endIdx, $elements)
    {
        parent::__construct($startIdx, $endIdx);
        $this->elements = $elements;
    }

    public function getElements()
    {
        return $this->elements;
    }
}