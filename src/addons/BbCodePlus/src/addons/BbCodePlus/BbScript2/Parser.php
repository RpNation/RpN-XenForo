<?php

namespace BbCodePlus\BbScript2;

use BbCodePlus\BbScript2\AST;

use Exception;

class Parser
{
    private $text = '';
    private $length = 0;
    private $errors = array();
    private $pos = 0;

    public function __construct($text)
    {
        $this->text = $text;
        $this->length = strlen($text);
    }

    public function buildAst()
    {
        $ast = array();
        $this->errors = array();
        $this->pos = 0;

        while ($this->pos < $this->length)
        {
            // Consume any leading whitespace
            $this->consumeWhitespace();
            if ($this->pos >= $this->length)
            {
                break;
            }

            // Read a top-level function, optionally surrounded by parentheses
            if ($this->head() == '(')
            {
                $node = $this->processFunctionCall();
            }
            else
            {
                $this->errors[] = new AST\ASTError(
                    null,
                    'Expecting function call, found ' . $this->head()
                );
                break;
            }

            // Only add a node if it was successfully built
            if ($node != NULL)
            {
                $ast[] = $node;
            }
        }

        return $ast;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function processFunctionCall()
    {
        $startIdx = $this->pos;

        // Consume the starting parentheses
        $this->pos++;

        // Consume the identifier
        $nameStartIdx = $this->pos;
        while (!$this->isWhitespaceChar() && $this->head() != ')')
        {
            $this->pos++;
        }
        $nameEndIdx = $this->pos;
        $name = new AST\Identifier(
            $nameStartIdx,
            $nameEndIdx,
            substr($this->text, $nameStartIdx, $nameEndIdx - $nameStartIdx)
        );
        $this->consumeWhitespace();

        // Validate the identifier
        foreach (str_split($name->getName()) as $c)
        {
            if (!$this->isFunctionChar($c))
            {
                $this->errors[] = new AST\ASTError($name, "Invalid function identifier");
            }
        }
        if (empty($name->getName()))
        {
            $this->errors[] = new AST\ASTError($name, "Missing function identifier");
        }

        // Consume the parameters
        $params = [];
        while (true)
        {
            // Get the next parameter if there are still parameters left
            $this->consumeWhitespace();
            $c = $this->head();
            if ($c == ')')
            {
                break;
            }

            // Build the parameter's node
            switch ($c)
            {
                case '(':
                    $params[] = $this->processFunctionCall();
                    break;

                case '"':
                    $params[] = $this->processString();
                    break;

                case '[':
                    $params[] = $this->processList();
                    break;

                default:
                    if (is_numeric($c) || $c == '-')
                    {
                        $params[] = $this->processNumber();
                    }
                    else
                    {
                        $params[] = $this->processIdentifier();
                    }
                    break;
            }
        }

        // Consume the ending parentheses
        $this->pos++;
        $endIdx = $this->pos;

        return new AST\FunctionCall($startIdx, $endIdx, $name, $params);
    }

    private function processList()
    {
        $startIdx = $this->pos;
        $this->pos++;

        $elements = [];
        while (true)
        {
            // Get the next element if there are still elementss left
            $this->consumeWhitespace();
            $c = $this->head();
            if ($c == ']')
            {
                break;
            }

            // Build the parameter's node
            switch ($c)
            {
                case '(':
                    $elements[] = $this->processFunctionCall();
                    break;

                case '"':
                    $elements[] = $this->processString();
                    break;

                case '[':
                    $elements[] = $this->processList();
                    break;

                default:
                    if (is_numeric($c) || $c == '-')
                    {
                        $elements[] = $this->processNumber(']');
                    }
                    else
                    {
                        $elements[] = $this->processIdentifier(']');
                    }
                    break;
            }
        }

        // Consume the ending
        $this->pos++;
        $endIdx = $this->pos;

        return new AST\ArrayList($startIdx, $endIdx, $elements);
    }

    private function processString()
    {
        $string = "";
        $startIdx = $this->pos;
        $this->pos++;

        $escaped = false;
        while (true)
        {
            $c = $this->head();
            if ($c == '"')
            {
                if (!$escaped)
                {
                    break;
                }
                $string .= $c;
                $escaped = false;
            }
            else if ($c == '\\')
            {
                if ($escaped)
                {
                    $string .= $c;
                }
                $escaped = !$escaped;
            }
            else
            {
                $string .= $c;
                $escaped = false;
            }
            $this->pos++;
        }
        $endIdx = $this->pos;
        $this->pos++;

        return new AST\QuotedString(
            $startIdx,
            $endIdx,
            str_replace("\n", '\n', $string)
        );
    }

    private function processNumber($terminator=')')
    {
        $startIdx = $this->pos;
        if ($this->head() == '-')
        {
            $this->pos++;
        }
        while (!$this->isWhitespaceChar() && $this->head() != $terminator)
        {
            $this->pos++;
        }
        $endIdx = $this->pos;
        $value = substr($this->text, $startIdx, $endIdx - $startIdx);
        $numberLiteral = new AST\NumberLiteral(
            $startIdx,
            $endIdx,
            $value
        );
        if (!is_numeric($value))
        {
            $this->errors[] = new AST\ASTError($numberLiteral, "Invalid number literal");
        }
        return $numberLiteral;
    }

    private function processIdentifier($terminator=')')
    {
        // Build the identifier
        $nameStartIdx = $this->pos;
        while (!$this->isWhitespaceChar() && $this->head() != $terminator)
        {
            $this->pos++;
        }
        $nameEndIdx = $this->pos;
        $identifier = new AST\Identifier(
            $nameStartIdx,
            $nameEndIdx,
            substr($this->text, $nameStartIdx, $nameEndIdx - $nameStartIdx)
        );

        // Validate the identifier
        foreach (str_split($identifier->getName()) as $c)
        {
            if (!ctype_alpha($c) && $c != '_')
            {
                $this->errors[] = new AST\ASTError($identifier, "Invalid identifier");
                break;
            }
        }
        if (empty($identifier->getName()))
        {
            $this->errors[] = new AST\ASTError($identifier, "Missing identifier");
        }
        return $identifier;
    }

    private function head($throw = true)
    {
        if ($this->pos < $this->length)
        {
            return $this->text[$this->pos];
        }
        if ($throw)
        {
            throw new Exception('Unexpected end of script');
        }
        return null;
    }

    private function consumeWhitespace()
    {
        if ($this->isWhitespaceChar())
        {
            do {
                $this->pos++;
            } while ($this->pos < $this->length && $this->isWhitespaceChar());
            return true;
        }
        return false;
    }

    private function isWhitespaceChar($char = null)
    {
        if ($char == null)
        {
            $char = $this->head();
        }
        return in_array($char, [' ', "\t", "\n", "\r"]);
    }

    private function isFunctionChar($char = null)
    {
        if ($char == null)
        {
            $char = $this->head();
        }
        return in_array($char, ['+', '-', '*', '/', '%', '_', '<', '>', '=', '!']) || ctype_alpha($char);
    }

    public static function parse($bbscript)
    {
        $parser = new Parser($bbscript);
        $ast = [];
        try
        {
            $ast = $parser->buildAst();
        }
        catch (Exception $e)
        {
            $fatalError = new AST\ASTError(null, $e->getMessage());
        }
        catch (\Throwable $e)
        {
            \XF::logError("Internal error in $bbscript: " . $e->getMessage(), true);
            $fatalError = new AST\ASTError(null, $e->getMessage());
        }

        $converter = new Converter($ast);
        $javascript = $converter->buildJs();

        $errors = array_merge($parser->getErrors(), $converter->getErrors());
        if (isset($fatalError))
        {
            $errors[] = $fatalError;
        }

        // Format the errors
        $formattedErrors = array();
        foreach ($errors as $error)
        {
            $formattedErrors[] = $error->format($bbscript);
        }

        return [
            'js' => $javascript,
            'errors' => $formattedErrors
        ];
    }
}