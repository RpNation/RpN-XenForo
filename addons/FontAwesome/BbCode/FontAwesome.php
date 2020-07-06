<?php

namespace FontAwesome\BbCode;

use XF\BbCode\Renderer\AbstractRenderer;

class FontAwesome
{
	private const FA_PREFIXES = ['far','fab','fas','fal'];

	public static function renderTagWithFontAwesome($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$text = htmlspecialchars($renderer->renderSubTreePlain($tagChildren));
		$parts = explode(' ', $text);
		$prefix_exists = false;
		foreach ($parts as $part)
		{
			if (in_array(strtolower($part), self::FA_PREFIXES))
			{
				$prefix_exists = true;
			}
		}
		if ($prefix_exists)
		{
			return "<i class=\"${text}\" aria-hidden=\"true\"></i>";
		}
		else
		{
			return "<i class=\"fa ${text}\" aria-hidden=\"true\"></i>";
		}
	}
}