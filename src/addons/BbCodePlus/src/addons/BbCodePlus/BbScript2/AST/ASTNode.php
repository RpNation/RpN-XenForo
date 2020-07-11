<?php

namespace BbCodePlus\BbScript2\AST;

class ASTNode
{
    private $startIdx;
    private $endIdx;

    public function __construct($startIdx, $endIdx)
    {
        $this->startIdx = $startIdx;
        $this->endIdx = $endIdx;
    }

    public function getStartIdx()
    {
        return $this->startIdx;
    }

    public function getEndIdx()
    {
        return $this->endIdx;
    }

    public function findInText($text)
    {
        $line = $column = 1;
        for ($idx = 0, $len = strlen($text); $idx < $len; $idx++)
        {
            if ($idx == $this->startIdx)
            {
                break;
            }
            if ($text[$idx] == "\n")
            {
                $line++;
                $column = 0;
            }
            $column++;
        }
        return [
            'line' => $line,
            'column' => $column,
            'text' => substr($text, $this->startIdx, $this->endIdx - $this->startIdx)
        ];
    }
}
