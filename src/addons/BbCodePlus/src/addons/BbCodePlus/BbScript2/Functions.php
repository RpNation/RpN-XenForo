<?php

namespace BbCodePlus\BbScript2;

class Functions
{
    private const FUNCTIONS = [
        // DOM-altering functions
        'addClass' => [
            'params' => [
                [],  // class
                ['default' => null]  // target
            ]
        ],
        'removeClass' => [
            'params' => [
                [],  // class
                ['default' => null]  // target
            ]
        ],
        'fadeIn' => [
            'params' => [
                ['default' => 1000],  // duration
                ['default' => null]  // target
            ]
        ],
        'fadeOut' => [
            'params' => [
                 ['default' => 1000],  // duration
                ['default' => null]  // target
            ]
        ],
        'fadeToggle' => [
            'params' => [
                ['default' => 1000], // duration
                ['default' => null]  // target
            ]
        ],
        'hide' => [
            'params' => [
                ['default' => null]  // target
            ]
        ],
        'show' => [
            'params' => [
                ['default' => null]  // target
            ]
        ],
        'getText' => [
            'params' => [
                ['default' => null]  // target
            ]
        ],
        'setText' => [
            'params' => [
                [],  // text
                ['default' => null]  // target
            ]
        ],
        'getVal' => [
            'params' => [
                ['default' => null]  // target
            ]
        ],
        'setVal' => [
            'params' => [
                [],  // val
                ['default' => null]  // target
            ]
        ],
        'slideDown' => [
            'params' => [
                ['default' => 1000],  // duration
                ['default' => null]  // target
            ]
        ],
        'slideUp' => [
            'params' => [
                ['default' => 1000],  // duration
                ['default' => null]  // target
            ]
        ],
        'slideToggle' => [
            'params' => [
                ['default' => 1000],  // duration
                ['default' => null]  // target
            ]
        ],
        'addDiv' => [
            'params' => [
                [],  // _class
                ['default' => null]  // targets
            ]
        ],
        'removeDiv' => [
            'params' => [
                [],  // _class
                ['default' => null]  // targets
            ]
        ],

        // Array (and string) manipulation functions
        'count' => [
            'params' => [
                []  // array
            ]
        ],
        'contains' => [
            'params' => [
                [],  // array
                []  // needle
            ]
        ],
        'find' => [
            'params' => [
                [],  // array
                []  // needle
            ]
        ],
        'index' => [
            'params' => [
                [],  // array
                [],  // index
                ['default' => null]  // value
            ]
        ],
        'append' => [
            'params' => [
                [],  // array
                []  // value
            ]
        ],
        'insert' => [
            'params' => [
                [],  // array
                [],  // index
                []  // value
            ]
        ],
        'pop' => [
            'params' => [
                []  // array
            ]
        ],
        'remove' => [
            'params' => [
                [],  // array
                []  // index
            ]
        ],
        'reverse' => [
            'params' => [
                []  // array
            ]
        ],
        'join' => [
            'params' => [
                [],  // array
                ['default' => null]  // separator
            ]
        ],
        'shuffle' => [
            'params' => [
                []  // array
            ]
        ],
        'slice' => [
            'params' => [
                [],  // array
                [],  // start
                []  // end
            ]
        ],
        'each' => [
            'function' => 'each',
            'params' => [
                [],  // array
                ['type' => AST\FunctionCall::class],  // function
            ]
        ],

        // String manipulation functions
        'split' => [
            'params' => [
                [],  // string
                ['default' => null]  // separator
            ]
        ],
        'lower' => [
            'params' => [
                []  // string
            ]
        ],
        'upper' => [
            'params' => [
                []  // string
            ]
        ],
        'trim' => [
            'params' => [
                []  // string
            ]
        ],
        'replace' => [
            'params' => [
                [],  // string
                [],  // needle
                [],  // replacement
            ]
        ],

        // Variable manipulation functions
        '=' => [
            'function' => 'opAssignment',
            'params' => [
                ['type' => AST\Identifier::class],  // variable
                []  // value
            ]
        ],
        '+' => [
            'function' => 'opAddition',
            'params' => [
                ['varargs' => true] // operands
            ]
        ],
        '-' => [
            'function' => 'opSubtraction',
            'params' => [
                ['varargs' => true] // operands
            ]
        ],
        '*' => [
            'function' => 'opMultiplication',
            'params' => [
                ['varargs' => true] // operands
            ]
        ],
        '/' => [
            'function' => 'opDivision',
            'params' => [
                ['varargs' => true] // operands
            ]
        ],
        '%' => [
            'function' => 'opRemainder',
            'params' => [
                [],  // operand1
                []  // operand2
            ]
        ],
        '**' => [
            'function' => 'opExponent',
            'params' => [
                [],  // operand1
                []  // operand2
            ]
        ],
        '--' => [
            'function' => 'opDecrement',
            'params' => [
                ['type' => AST\Identifier::class]  // variable
            ]
        ],
        '++' => [
            'function' => 'opIncrement',
            'params' => [
                ['type' => AST\Identifier::class]  // variable
            ]
        ],
        'and' => [
            'function' => 'opAnd',
            'params' => [
                ['varargs' => true]  // operands
            ]
        ],
        'or' => [
            'function' => 'opOr',
            'params' => [
                ['varargs' => true]  // operands
            ]
        ],

        // Comparison functions
        '==' => [
            'function' => 'opEqual',
            'params' => [
                [],  // operand1
                []  // operand2
            ]
        ],
        '!=' => [
            'function' => 'opNotEqual',
            'params' => [
                [],  // operand1
                []  // operand2
            ]
        ],
        '>' => [
            'function' => 'opGreaterThan',
            'params' => [
                [],  // operand1
                []  // operand2
            ]
        ],
        '>=' => [
            'function' => 'opGreaterThanOrEqual',
            'params' => [
                [],  // operand1
                []  // operand2
            ]
        ],
        '<' => [
            'function' => 'opLessThan',
            'params' => [
                [],  // operand1
                []  // operand2
            ]
        ],
        '<=' => [
            'function' => 'opLessThanOrEqual',
            'params' => [
                [],  // operand1
                []  // operand2
            ]
        ],

        // Flow control
        'if' => [
            'function' => 'if',
            'params' => [
                [],  // test
                ['type' => AST\FunctionCall::class],  // caseTrue
                ['type' => AST\FunctionCall::class, 'default' => null]  // caseFalse
            ]
        ],
        'group' => [
            'function' => 'group',
            'params' => [
                ['type' => AST\FunctionCall::class, 'varargs' => true]  // functionCalls
            ]
        ],
        'stop' => [
            'function' => 'stop',
            'params' => []
        ],

        // RNG functions
        'random' => [
            'params' => []
        ],
        'randomInt' => [
            'params' => [
                [],  // min
                []  // max
            ]
        ],

        // Time functions
        'time' => [],
        'setTimeout' => [
            'function' => 'setTimeout',
            'params' => [
                [],  // seconds
                ['type' => AST\FunctionCall::class]  // function
            ]
        ],
        'clearTimeout' => [
            'params' => [
                []  // handle
            ]
        ],
        'setInterval' => [
            'function' => 'setInterval',
            'params' => [
                [],  // seconds
                ['type' => AST\FunctionCall::class]  // function
            ]
        ],
        'clearInterval' => [
            'params' => [
                []  // handle
            ]
        ],

        // Debug functions
        'print' => [
            'params' => [
                []  // message
            ]
        ]
    ];

    public static function callFunction(AST\FunctionCall $functionCall)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $name = $functionCall->getIdentifier()->getName();
        if (!array_key_exists($name, self::FUNCTIONS))
        {
            throw new AST\ASTError($functionCall->getIdentifier(), 'Unknown function name');
        }
        $entry = self::FUNCTIONS[$name];

        // Consume any variable arguments
        if (!empty(end($entry['params'])['varargs']))
        {
            $varargsParam = array();
            $varargsEntry = end($entry['params']);
            $params = $functionCall->getParams();
            for ($i = count($entry['params']) - 1; $i < count($params); $i++)
            {
                $param = $params[$i];
                if (array_key_exists('type', $varargsEntry) && get_class($param) != $varargsEntry['type'])
                {
                    throw new AST\ASTError($functionCall, 'Unexpected type for parameter ' . ($i + 1));
                }
                $varargsParam[] = $param;
            }
            $actualParams = array_slice($functionCall->getParams(), 0, count($entry['params']) - 1);
            $actualParams[] = $varargsParam;
        }
        else
        {
            if (count($entry['params']) < count($functionCall->getParams()))
            {
                throw new AST\ASTError($functionCall, 'Too many parameters, expected ' . count($entry['params']) . ' at most');
            }
            $actualParams = $functionCall->getParams();
        }

        // Add any default parameters
        for ($i = count($actualParams); $i < count($entry['params']); $i++)
        {
            $param = $entry['params'][$i];

            if (!array_key_exists('default', $param))
            {
                throw new AST\ASTError($functionCall, 'Missing parameter ' . ($i + 1));
            }
            $default = $param['default'];
            if ($default == null)
            {
                $actualParams[] = null;
            }
            elseif (is_int($default))
            {
                $actualParams[] = new AST\NumberLiteral($functionCall->getStartIdx(), $functionCall->getEndIdx(), $default);
            }
        }

        // Validate the parameter types
        for ($i = 0; $i < count($entry['params']); $i++)
        {
            $actual = $actualParams[$i];
            $param = $entry['params'][$i];
            if (!empty($param['varargs']))
            {
                break;
            }

            if (array_key_exists('type', $param))
            {
                if ($actual != null && !is_a($actual, $param['type']))
                {
                    throw new AST\ASTError($functionCall, 'Unexpected type for parameter ' . ($i + 1));
                }
            }
        }

        if (array_key_exists('function', $entry))
        {
            return call_user_func_array(['BbCodePlus\BbScript2\Functions', $entry['function']], $actualParams);
        }
        else
        {
            $jsParams = '';
            foreach ($actualParams as $param)
            {
                $jsParams .= ',' . self::_toJs($param);
            }
            return 'bb_' . $name . '(id,_this' . $jsParams . ')';
        }
    }

    private static function _toJs($node)
    {
        if ($node == null)
        {
            return 'null';
        }
        switch (get_class($node))
        {
            case AST\FunctionCall::class:
                return self::callFunction($node);
            case AST\Identifier::class:
                return self::_toJsVariable($node);
            case AST\QuotedString::class:
                /** @noinspection PhpUndefinedMethodInspection */
                return self::_toJsString($node->getString());
            case AST\NumberLiteral::class:
                /** @noinspection PhpUndefinedMethodInspection */
                return $node->getValue();
            case AST\ArrayList::class:
                return self::_toJsArray($node);
            default:
                throw new AST\ASTError($node, 'Unexpected type');
        }
    }

    private static function _toJsString(string $value)
    {
        $escapedValue = addcslashes(str_replace('\\"', '\\\\"', $value), '"');
        return '"' . $escapedValue . '"';
    }

    private static function _toJsVariable(AST\Identifier $value)
    {
        $varName = self::_toJsString($value->getName());
        return "bb_getVar(id,$varName)";
    }

    private static function _toJsArray(AST\ArrayList $value)
    {
        $jsElements = array();
        foreach ($value->getElements() as $element)
        {
            $jsElements[] = self::_toJs($element);
        }
        return '[' . implode(',', $jsElements) . ']';
    }

    private static function _op(string $operator, AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return '(' . self::_toJs($operand1) . " $operator " . self::_toJs($operand2) . ')';
    }

    private static function _varOp(string $operator, array $operands)
    {
        $jsOperands = array();
        foreach ($operands as $operand)
        {
            $jsOperands[] = self::_toJs($operand);
        }
        return '(' . implode($operator, $jsOperands) . ')';
    }

    private static function each($array, AST\FunctionCall $function)
    {
        $jsArray = self::_toJs($array);
        $jsFunction = self::_toJs($function);
        return "var arr=$jsArray;for(var i=0;i<arr.length;i++){bb_setVar(id,'_',arr[i]);$jsFunction;bb_setVar(id,'_','');}";
    }

    private static function opAssignment(AST\Identifier $identifier, AST\ASTNode $value)
    {
        $varName = self::_toJsString($identifier->getName());
        $jsValue = self::_toJs($value);
        return "bb_setVar(id,$varName,$jsValue)";
    }

    private static function opAddition(array $operands)
    {
        return self::_varOp('+', $operands);
    }

    private static function opSubtraction(array $operands)
    {
        return self::_varOp('-', $operands);
    }

    private static function opMultiplication(array $operands)
    {
        return self::_varOp('*', $operands);
    }

    private static function opDivision(array $operands)
    {
        return self::_varOp('/', $operands);
    }

    private static function opRemainder(AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return self::_op('%', $operand1, $operand2);
    }

    private static function opExponent(AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return self::_op('**', $operand1, $operand2);
    }

    private static function opIncrement(AST\Identifier $variable)
    {
        $varName = self::_toJsString($variable->getName());
        $jsVar = self::_toJs($variable);
        return "bb_setVar(id,$varName,$jsVar+1)";
    }

    private static function opDecrement(AST\Identifier $variable)
    {
        $varName = self::_toJsString($variable->getName());
        $jsVar = self::_toJs($variable);
        return "bb_setVar(id,$varName,$jsVar-1)";
    }

    private static function opAnd(array $operands)
    {
        return self::_varOp('&&', $operands);
    }

    private static function opOr(array $operands)
    {
        return self::_varOp('||', $operands);
    }

    private static function opEqual(AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return self::_op('==', $operand1, $operand2);
    }

    private static function opNotEqual(AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return self::_op('!=', $operand1, $operand2);
    }

    private static function opGreaterThan(AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return self::_op('>', $operand1, $operand2);
    }

    private static function opGreaterThanOrEqual(AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return self::_op('>=', $operand1, $operand2);
    }

    private static function opLessThan(AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return self::_op('<', $operand1, $operand2);
    }

    private static function opLessThanOrEqual(AST\ASTNode $operand1, AST\ASTNode $operand2)
    {
        return self::_op('<=', $operand1, $operand2);
    }

    private static function if(AST\ASTNode $test, AST\FunctionCall $caseTrue, AST\FunctionCall $caseFalse = null)
    {
        $jsTest = self::_toJs($test);
        $jsTrue = self::_toJs($caseTrue);
        $if = "if ($jsTest) { $jsTrue }";
        if ($caseFalse != null)
        {
            $jsFalse = self::_toJs($caseFalse);
            $if .= " else { $jsFalse }";
        }
        return $if;
    }

    private static function group(array $functionCalls)
    {
        $block = '';
        foreach ($functionCalls as $functionCall)
        {
            $block .= self::_toJs($functionCall) . ';';
        }
        return $block;
    }

    private static function stop()
    {
        return "return";
    }

    private static function setTimeout(AST\ASTNode $seconds, AST\FunctionCall $function)
    {
        $jsSeconds = self::_toJs($seconds);
        $jsFunction = self::_toJs($function);
        return "setTimeout(function() { $jsFunction; }, Math.round($jsSeconds)*1000)";
    }

    private static function setInterval(AST\ASTNode $seconds, AST\FunctionCall $function)
    {
        $jsSeconds = self::_toJs($seconds);
        $jsFunction = self::_toJs($function);
        return "setInterval(function() { $jsFunction; }, Math.round($jsSeconds)*1000)";
    }
}