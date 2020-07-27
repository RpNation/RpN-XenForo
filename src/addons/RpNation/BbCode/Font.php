<?php

namespace RpNation\BbCode;

use XF\BbCode\Renderer\AbstractRenderer;

class Font
{
	private const FONTS = array(
		'arial', 'book antiqua', 'courier new', 'georgia', 'tahoma', 'times new roman', 'trebuchet ms', 'verdana'
	);

	private const STYLES = array(
		'thin', 'extralight', 'light', 'regular', 'medium', 'semibold', 'bold', 'extrabold', 'black', 'italic'
	);

	private const WEIGHTS = array(
		'100', '200', '300', '400', '500', '600', '700', '800', '900','i'
	);

	public static function renderFontTag($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$text = $renderer->renderSubTree($tagChildren, $options);
		$font = null;
		$style = null;
		$fontweight = '400';
		$fontstyle = 'normal';
		
		if (is_array($tagOption))
		{
			$font = $tagOption['name'] ?? $tagOption['family'] ?? "";
			$style = $tagOption['style'] ?? null;
		}
		else
		{
			$font = $tagOption;
		}
		
		if (empty(trim($text)) || empty($font))
		{
			return $text;
		}
		$font = strtolower(trim($font));

		if (in_array($font, self::FONTS))
		{
			return "<span style=\"font-family: '$font'\">" . $text . "</span>";
		}

		$font = htmlspecialchars(addslashes($font));
		if (is_subclass_of($renderer, 'XF\BbCode\Renderer\Html'))
		{
			$webfont = str_replace(' ', '+', $font);
			if ($style != null)
			{
				$style = str_replace(self::STYLES, self::WEIGHTS, str_replace([' ', '-'], '', strtolower(trim($style))));
				$webfont .= ':' . $style;
				if (stripos($style, 'i') !== false)
				{
					$fontstyle = "italic";
					$style = str_replace('i', '', $style);
				}
				if (strlen($style) !== 0)
				{
					$fontweight = trim($style);
				}
			}
			$renderer->getTemplater()->inlineJs("loadWebfont('$webfont');");
		}
		return "<span style=\"font-family: '$font'; font-style: '$fontstyle'; font-weight: '$fontweight';\">$text</span>";
	}
}