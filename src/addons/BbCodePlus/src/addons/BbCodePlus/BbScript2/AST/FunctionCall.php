<?php

namespace BbCodePlus\BbScript2\AST;

class FunctionCall extends ASTNode
{
    private
        $identifier;
    private $params;

    public function __construct($startIdx, $endIdx, $name, $params)
    {
        parent::__construct($startIdx, $endIdx);
        $this->identifier = $name;
        $this->params = $params;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getParams()
    {
        return $this->params;
    }
}