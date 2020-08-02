<?php

namespace RpNation\BbCode;

use XF\BbCode\Renderer\AbstractRenderer;

class Font
{
	private const FONTS = array(
		'arial', 'book antiqua', 'courier new', 'georgia', 'tahoma', 'times new roman', 'trebuchet ms', 'verdana'
	);

	public static function renderFontTag($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$text = $renderer->renderSubTree($tagChildren, $options);
		$font = strtolower(trim($tagOption));

		if (empty(trim($text)) || empty($font))
		{
			return $text;
		}

		if (in_array(strtolower(trim($font)), self::FONTS))		//matches default XF base fonts
		{
			return "<span style=\"font-family: '$font'\">" . $text . "</span>";
		}

		$font = htmlspecialchars(addslashes(trim($tagOption)));
		if (is_subclass_of($renderer, 'XF\BbCode\Renderer\Html'))
		{
			$webfont = str_replace(' ', '+', $font);
			$renderer->getTemplater()->inlineJs("loadWebfont('$webfont');");
		}
		return "<span style=\"font-family: '$font';\">$text</span>";

	}
}