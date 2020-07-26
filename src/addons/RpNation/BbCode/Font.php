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

		if (in_array($font, self::FONTS))
		{
			return "<span style=\"font-family: '$font'\">" . $text . "</span>";
		}

		$font = htmlspecialchars(addslashes(trim($tagOption)));

		// $renderer->getTemplater()->inlineJs("console.log($renderer['type'])");
		$test = is_subclass_of($renderer, 'XF\BbCode\Renderer\Html');
		$renderer->getTemplater()->inlineJs("console.log('test: ', $test)");
		if (is_subclass_of($renderer, 'XF\BbCode\Renderer\Html'))
		{
			$renderer->getTemplater()->inlineJs("console.log('here')");
			$webfont = str_replace(' ', '+', $font);
			$renderer->getTemplater()->inlineJs("loadWebfont('$webfont');");
		}
		return "<span style=\"font-family: '$font';\">$text</span>";
	}
}