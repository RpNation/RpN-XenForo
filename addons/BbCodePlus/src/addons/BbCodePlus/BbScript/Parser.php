<?php

namespace BbCodePlus\BbScript;

use Exception;

class Parser
{
	public static function parse($bbscript)
	{
		$result = [
			'js' => '',
			'errors' => []
		];

		// Build the JavaScript
		$lines = explode("\n", trim($bbscript));
		foreach ($lines as $n => $line)
		{
			$line = trim($line);
			if (empty($line) || substr($line, 0, 2) === '//')
			{
				continue;
			}
			try
			{
				$node = Functions::_process($line);
				$result['js'] .= Functions::_convert($node) . ';';
			}
			catch (Exception $e)
			{
				$result['errors'][] = "Line " . ($n + 1) . ": " . $e->getMessage();
			}
            catch (\Throwable $e)
            {
                \XF::logError("Internal error in $line: " . $e->getMessage(), true);
                $result['errors'][] = "Line " . ($n + 1) . ": " . $e->getMessage();
            }
		}

		return $result;
	}
}