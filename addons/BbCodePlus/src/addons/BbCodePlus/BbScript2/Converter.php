<?php

namespace BbCodePlus\BbScript2;

class Converter
{
    private $ast;
    private $errors;

    public function __construct($ast)
    {
        $this->ast = $ast;
        $this->errors = array();
    }

    public function buildJs()
    {
        $this->errors = array();
        $javascript = '';

        foreach ($this->ast as $functionCall)
        {
            $javascript .= $this->outputFunctionCall($functionCall);
        }

        return $javascript;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function outputFunctionCall(AST\FunctionCall $functionCall)
    {
        try
        {
            $output = Functions::callFunction($functionCall) . ';';
        }
        catch (AST\ASTError $error)
        {
            $this->errors[] = $error;
            return '';
        }

        return $output;
    }
}