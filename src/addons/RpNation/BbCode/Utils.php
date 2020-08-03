<?php

namespace RpNation\BbCode;

use XF\BbCode\Renderer\AbstractRenderer;

class Utils
{
	private const HTTP_URL = '#(url\s*\(\s*(?:["\']|&quot;)?\s*)(https?:.+?)(\s*(?:["\']|&quot;)?\s*\))#i';

	private const TAGS = [
		'bg' => '<div class="bbcode-background" style="background-color: {option};"><div class="bbcode-background-text">{text}</div></div>',
		'border' => '<div class="bbcode-border" style="border: {option};">{text}</div>',
		'centerblock' => '<div class="bbcode-centerblock" style="width: {option}%;">{text}</div>',
		'heightrestrict' => '<div class="bbcode-height-restrict" style="height: {option}px;">{text}</div>',
		'progress' => '<div class="bbcode-progress">' .
			'<div class="bbcode-progress-text">{text}</div>' .
			'<div class="bbcode-progress-bar" style="width: calc({option}% - 6px);"></div>' .
			'<div class="bbcode-progress-bar-other"></div></div>',
		'scroll' => '<div style="max-width:100%; height:{option}; padding:5px; overflow:auto; border: 1px solid;">{text}</div>',
		'thinprogress' => '<div class="bbcode-progress-thin">' .
			'<div class="bbcode-progress-text">{text}</div>' .
			'<div class="bbcode-progress-bar" style="width: {option}%"></div>' .
			'<div class="bbcode-progress-bar-other"></div></div>'
	];

	public static function renderTagWithProxiedCss($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$tagName = $tag['tag'];
		if (!array_key_exists(strtolower($tagName), self::TAGS))
		{
			return "Error: invalid tag '$tagName'";
		}
		$text = $renderer->renderSubTree($tagChildren, $options);

		return strtr(self::TAGS[strtolower($tagName)], [
			'{text}' => $text,
			'{option}' => self::processCss($tagOption, $options, $renderer)
		]);
	}

	private static function processCss($css, $options, $renderer) {
		// Only proxy if we are rendering to Html
		if (empty($options['noProxy']) && get_class($renderer) == 'BbCodePlus\XF\BbCode\Renderer\Html')
		{
			return preg_replace_callback(self::HTTP_URL, function ($matches) use($renderer) {
				return $matches[1] . $renderer->getProxiedImageIfActive($matches[2]) . $matches[3];
			}, htmlspecialchars($css));
		}

		// Otherwise, don't bother performing any replacements, since they won't be in important areas.
		return htmlspecialchars($css);
	}
}