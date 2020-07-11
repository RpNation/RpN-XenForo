<?php

namespace BbCodePlus\BbScript;

use Exception;

/**
 * BBScript function definitions and parsing utilities.
 *
 * @package BbCodePlus\BbScript
 */
class Functions
{
	private const FUNCTIONS = [
		// DOM-altering functions
		'addClass' => [
			['type' => ['identifier']],
			['type' => ['identifier'], 'default' => null]
		],
		'removeClass' => [
			['type' => ['identifier']],
			['type' => ['identifier'], 'default' => null]
		],
		'fadeIn' => [
			['type' => ['int'], 'default' => 1000],
			['type' => ['identifier'], 'default' => null]
		],
		'fadeOut' => [
			['type' => ['int'], 'default' => 1000],
			['type' => ['identifier'], 'default' => null]
		],
		'fadeToggle' => [
			['type' => ['int'], 'default' => 1000],
			['type' => ['identifier'], 'default' => null]
		],
		'hide' => [
			['type' => ['identifier'], 'default' => null]
		],
		'show' => [
			['type' => ['identifier'], 'default' => null]
		],
		'getText' => [
			['type' => ['identifier'], 'default' => null]
		],
		'setText' => [
			['type' => ['string', 'function']],
			['type' => ['identifier'], 'default' => null]
		],
		'getVal' => [
			['type' => ['identifier'], 'default' => null]
		],
		'setVal' => [
			['type' => ['string', 'function']],
			['type' => ['identifier'], 'default' => null]
		],
		'slideDown' => [
			['type' => ['int'], 'default' => 1000],
			['type' => ['identifier'], 'default' => null]
		],
		'slideToggle' => [
			['type' => ['int'], 'default' => 1000],
			['type' => ['identifier'], 'default' => null]
		],
		'slideUp' => [
			['type' => ['int'], 'default' => 1000],
			['type' => ['identifier'], 'default' => null]
		],

		// Variable manipulation functions
		'dec' => [
			['type' => ['identifier']],
			['type' => ['int'], 'default' => 1]
		],
		'inc' => [
			['type' => ['identifier']],
			['type' => ['int'], 'default' => 1]
		],
		'set' => [
			['type' => ['identifier']],
			['type' => ['string', 'int', 'function']]
		],

		// Flow control
		'if' => [
			['type' => ['function']],
			['type' => ['function']],
			['type' => ['function'], 'default' => null]
		],
		'stop' => [],

		// Returning functions
		'eq' => [
			['type' => ['int', 'string']],
			['type' => ['int', 'string']]
		],
		'ge' => [
			['type' => ['int', 'string']],
			['type' => ['int', 'string']]
		],
		'geq' => [
			['type' => ['int', 'string']],
			['type' => ['int', 'string']]
		],
		'le' => [
			['type' => ['int', 'string']],
			['type' => ['int', 'string']]
		],
		'leq' => [
			['type' => ['int', 'string']],
			['type' => ['int', 'string']]
		],
		'random' => [
			['type' => ['int']],
			['type' => ['int']]
		]
	];

	/**
	 * A function string to process.
	 *
	 * @param string $functionString The function to process.
	 * @return array The processed function's name and arguments.
	 * @throws Exception If an error occurred during parsing.
	 */
	public static function _process($functionString)
	{
		// Get the function's details
		$function = preg_split('#\s+#', $functionString, 2);
		$name = $function[0];
		if (count($function) > 1) {
            $params = trim($function[1]);
        } else {
		    $params = '';
        }
		$len = strlen($params);
		$ast = [
			'name' => $name,
			'params' => []
		];

		// Get the function's signature definition.
		if (!array_key_exists($name, self::FUNCTIONS))
		{
			throw new Exception("Invalid function name '$name'");
		}
		$signature = self::FUNCTIONS[$name];

		// Validate the function call.
		$idx = 0;
		foreach ($signature as $p => $param)
		{
			// Check if the end of the string has been reached
			$n = $p + 1;
			if ($idx >= $len)
			{
				// A parameter is missing
				if (!array_key_exists('default', $param))
				{
					throw new Exception("Missing parameter $n");
				}
				$ast['params'][] = $param['default'];
				continue;
			}

			// Get the parameter.
			if ($params[$idx] == '"')
			{
				// Process a quoted string
				$value = self::_getEnclosedParameter($params, $idx, $p);
			}
			elseif (in_array('function', $param['type']) && $params[$idx] == '(')
			{
				// Process an inner function
				try
				{
					$value = self::_process(self::_getEnclosedParameter($params, $idx, $p, ')'));
				}
				catch (\Exception $e)
				{
					throw new Exception("Error in function parameter $n: " . $e->getMessage());
				}
                catch (\Throwable $e)
                {
                    \XF::logError("Internal error in function parameter $n: " . $e->getMessage(), true);
                    throw new Exception("Internal error in function parameter $n");
                }
			}
			else
			{
				$remainder = substr($params, $idx);
				$v = preg_split('#\s+#', $remainder, 2);
				$value = $v[0];
				$idx += strlen($value);
			}

			// Check that the value is of the expected type
			if (in_array('int', $param['type']) && is_numeric($value))
			{
				$value = intval($value);
			}
			elseif (in_array('function', $param['type']) && is_array($value))
			{
				try
				{
					$value = self::_convert($value);
				}
				catch (Exception $e)
				{
					throw new Exception("Error in function parameter $n: " . $e->getMessage());
				}
                catch (\Throwable $e)
                {
                    \XF::logError("Internal error in function parameter $n: " . $e->getMessage(), true);
                    throw new Exception("Internal error in function parameter $n");
                }
			}
			elseif (in_array('string', $param['type']))
			{
				$value = self::_processString($value);
			}
			elseif (in_array('identifier', $param['type']))
			{
				if (!preg_match('#^\w+$#', $value))
				{
					throw new Exception("Error in function parameter $n: "
						. "Invalid variable name: can only contain letters, numbers and underscores");
				}
				$value = "'$value'";
			}
			else
			{
				throw new Exception("Invalid parameter type for parameter $n");
			}

			// Add the param to the list
			$ast['params'][] = $value;

			// Continue to the next parameter
			while (isset($params[$idx]) && preg_match('/\s/', $params[$idx]))
			{
				$idx++;
			}
		}
		if (!empty(substr($params, $idx)))
		{
			throw new Exception("Unexpected extra parameter");
		}

		return $ast;
	}

	/**
	 * Converts a function node to its JavaScript code.
	 *
	 * @param array $node The function node to convert.
	 * @return mixed The JavaScript code.
	 */
	public static function _convert(array $node)
	{
		$function = $node['name'];
		return self::$function($node['params']);
	}

	private static function _getEnclosedParameter(string $params, int &$idx, int $p, string $close = "\"")
	{
		$startIdx = $idx;
		$remainder = substr($params, $startIdx);
		$len = strlen($params);
		$idx++;
		while ($idx < $len)
		{
			$closeIndex = \strpos($params, $close, $idx);
			if ($closeIndex === false)
			{
				$idx = $len;
				break;
			}

			// escaped close; skip
			if ($params[$closeIndex - 1] === '\\')
			{
				$idx = $closeIndex + 1;
				continue;
			}

			$idx = $closeIndex;

			break;
		}
		if ($idx >= $len)
		{
			throw new Exception("Syntax error for parameter " . ($p+1) . ": missing $close");
		}
		$value = substr($remainder, 1, $idx - $startIdx - 1);
		$idx++;

		// Ensure the parameter ends after the string is closed.
		if ($idx != $len && !preg_match('#\s#', $params[$idx]))
		{
			throw new Exception("Syntax error for parameter " . ($p+1) . ": unexpected text after $close");
		}

		return $value;
	}

	private static function _processString($string) {
		// Check if this is a single variable
		if (preg_match('#^\$\{(\w+)\}$#', $string, $matches))
		{
			return "getBbScriptVar(id,'$matches[1]')";

		}

		$output = preg_replace_callback('#\$\{(\w+)\}#', function($matches) {
			return "'+getBbScriptVar(id,'$matches[1]')+'";
		}, str_replace('\'', '\\\'', $string));
		$output = str_replace("+''", "", str_replace("''+", "", "'" . $output . "'"));
		return $output;
	}

	private static function _simpleApplyToTarget($function, $params) {
		if (count($params) == 2) {
			$value = $params[0];
			$target = self::_getTarget($params[1]);
		} else {
			$value = '';
			$target = self::_getTarget($params[0]);
		}
		return "$($target).${function}(${value})";
	}

	private static function _getTarget($target, $selector = true) {
		return $target != null ? ($selector ? "'." : "'") . "p'+id+'-'+$target" : 'this';
	}

	private static function _test($params, $sign) {
		return "$params[0] $sign $params[1]";
	}

	private static function addClass($params) {
		$current = self::_getTarget($params[1]);
		$new = self::_getTarget($params[0], false);
		return "$($current).addClass($new)";
	}

	private static function removeClass($params) {
		$current = self::_getTarget($params[1]);
		$toRemove = self::_getTarget($params[0], false);
		return "$($current).removeClass($toRemove)";
	}

	private static function fadeIn($params) {
		return self::_simpleApplyToTarget("fadeIn", $params);
	}

	private static function fadeOut($params) {
		return self::_simpleApplyToTarget("fadeOut", $params);
	}

	private static function fadeToggle($params) {
		return self::_simpleApplyToTarget("fadeToggle", $params);
	}

	private static function hide($params) {
		return self::_simpleApplyToTarget("hide", $params);
	}

	private static function show($params) {
		return self::_simpleApplyToTarget("show", $params);
	}

	private static function getText($params) {
		return self::_simpleApplyToTarget("text", $params);
	}

	private static function setText($params) {
		return self::_simpleApplyToTarget("text", $params);
	}

	private static function getVal($params) {
		return self::_simpleApplyToTarget("val", $params);
	}

	private static function setVal($params) {
		return self::_simpleApplyToTarget("val", $params);
	}

	private static function slideDown($params) {
		return self::_simpleApplyToTarget("slideDown", $params);
	}

	private static function slideToggle($params) {
		return self::_simpleApplyToTarget("slideToggle", $params);
	}

	private static function slideUp($params) {
		return self::_simpleApplyToTarget("slideUp", $params);
	}

	private static function dec($params) {
		$variable = $params[0];
		$decrement = $params[1];
		return "setBbScriptVar(id,$variable,getBbScriptVar(id,$variable)-$decrement)";
	}

	private static function inc($params) {
		$variable = $params[0];
		$increment = $params[1];
		return "setBbScriptVar(id,$variable,getBbScriptVar(id,$variable)+$increment)";
	}

	private static function set($params) {
		$variable = $params[0];
		$value = $params[1];
		return "setBbScriptVar(id,$variable,$value)";
	}

	private static function if($params) {
		$testFunction = $params[0];
		$trueFunction = $params[1];
		$falseFunction = $params[2];
		$if = "if($testFunction){" . $trueFunction . "}";
		if ($falseFunction != null)
		{
			$if .= "else{" .  $falseFunction . "}";
		}
		return $if;
	}

	private static function stop($params) {
		return "return";
	}

	private static function eq($params) {
		return self::_test($params, '==');
	}

	private static function ge($params) {
		return self::_test($params, '>');
	}

	private static function geq($params) {
		return self::_test($params, '>=');
	}

	private static function le($params) {
		return self::_test($params, '<');
	}

	private static function leq($params) {
		return self::_test($params, '<=');
	}

	private static function random($params) {
		$min = $params[0];
		$max = $params[1];
		return "(Math.floor(Math.random()*($max-$min+1))+$min)";
	}
}