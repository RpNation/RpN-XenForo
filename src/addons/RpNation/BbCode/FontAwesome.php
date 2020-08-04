<?php

namespace RpNation\BbCode;

use XF\BbCode\Renderer\AbstractRenderer;

class FontAwesome
{
	private const FA_PREFIXES = ['far','fab','fas','fal'];		//fad tested separately

	public static function renderTagWithFontAwesome($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$text = htmlspecialchars($renderer->renderSubTreePlain($tagChildren));
		$parts = explode(' ', strtolower($text));
		$prefix_exists = false;
		$duotone = false;
		$duotone_options= "";

		foreach ($parts as $part)
		{
			if (in_array($part, self::FA_PREFIXES))
			{
				$prefix_exists = true;
				break;
			}
			if ($part == "fad")
			{
				$duotone = true;
				break;
			}
		}
		if ($duotone)
		{
			if (preg_match("/((.*)fa\-primary\-color\{(?P<firstcolor>.*?)\})/i", $text, $first_color))		
			{
				$duotone_options .= "\-\-fa\-primary\-color\: " . $first_color[firstcolor] . "\;";					//fa-primary-color{}
				preg_replace("/(fa\-primary\-color\{.*?\})/i", "", $text);
			}
			if (preg_match("/((.*)fa\-secondary\-color\{(?P<secondcolor>.*?)\})/i", $text, $second_color))			//fa-secondary-color{}
			{
				$duotone_options .= "\-\-fa\-secondary\-color\: " . $second_color[secondcolor] . "\;";
				preg_replace("/(fa\-secondary\-color\{.*?\})/i", "", $text);
			}
			if (preg_match("/((.*)fa\-primary\-opacity\{(?P<firstopacity>.*?)\})/i", $text, $first_opacity))		//fa-primary-opacity{}
			{
				$duotone_options .= "\-\-fa\-primary\-opacity\: " . $first_opacity[firstopacity] . "\;";
				preg_replace("/(fa\-primary\-opacity\{.*?\})/i", "", $text);
			}
			if (preg_match("/((.*)fa\-secondary\-opacity\{(?P<secondopacity>.*?)\})/i", $text, $second_opacity))	//fa-secondary-opacity{}
			{
				$duotone_options .= "\-\-fa\-secondary\-opacity\: " . $second_opacity[secondopacity] . "\;";
				preg_replace("/(fa\-secondary\-opacity\{.*?\})/i", "", $text);
			}
			return "<i class=\"${text}\" aria-hidden=\"true\" style=\"${duotone_options}\"></i>";
		}
		elseif ($prefix_exists)
		{
			return "<i class=\"${text}\" aria-hidden=\"true\"></i>";
		}
		else
		{
			return "<i class=\"fa ${text}\" aria-hidden=\"true\"></i>";
		}
	}
}