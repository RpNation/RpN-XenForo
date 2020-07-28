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
		$fontstyle = 'normal';
		$wght = null;
		$ital = null;
		
		if (is_array($tagOption))		//expected [font name="fontname" style="weight"]
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

		if (in_array($font, self::FONTS))		//matches default XF base fonts
		{
			return "<span style=\"font-family: '$font'\">" . $text . "</span>";
		}
    
		$font = htmlspecialchars(addslashes($font));
		if (is_a($renderer, 'XF\BbCode\Renderer\Html'))
		{
			$webfont = str_replace(' ', '+', $font);
			if ($style != null)			//tag contains style argument
			{
				/* possible combinations: "weight" "###" "italic" "weight italic" "### italic" "weight ###" "weight ### italic" */
				$style = str_replace(self::STYLES, self::WEIGHTS, str_replace('-', '', strtolower(trim($style))));

				$wght = (preg_match('/[0-9]{3}/', $style, array $matches) ? end($matches) : 400;
				$ital = (stripos($style, 'italic') !== false) ? 1 : 0;
				$fontstyle = ($ital === 1) ? 'italic' : 'normal';
				$webfont .= ":ital,wght@$ital,$wght";
			}
			$renderer->getTemplater()->inlineJs("loadWebfont('$webfont');");
		}
		return "<span style=\"font-family: '$font'; font-style: '$fontstyle'; font-weight: '$wght';\">$text</span>";
	}
}