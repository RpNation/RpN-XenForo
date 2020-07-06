<?php

namespace BbCodePlus\BbScript2\AST;

class Identifier extends ASTNode
{
    private $name;

    public function __construct($startIdx, $endIdx, $name)
    {
        parent::__construct($startIdx, $endIdx);
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}