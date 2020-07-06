<?php

namespace BbCodePlus\BbCode;

use BbCodePlus\XF\BbCode\Renderer\Html;

/**
 * Utility functions for BBCode+.
 *
 * @package BbCodePlus\BbCode
 */
class Utils
{
	private const URL = '#(url\s*\(\s*(?:["\']|&quot;)?\s*)(https?:\/\/.+?)(\s*(?:["\']|&quot;)?\s*\))#i';

	/**
	 * Processes a sequence of space separated class names.
	 *
	 * @param mixed $classes The classes to process
	 * @param mixed $renderer The renderer in use.
	 * @return string A sequence of processed, space-separated class names.
	 */
	public static function processClassNames($classes, $renderer)
	{
	    if ($classes == null || !Utils::supportsBBCodePlus($renderer))
        {
            return null;
        }

		$processedClasses = [];
		foreach (explode(' ', $classes) as $class)
		{
			$processedClass = self::processClassName($class, $renderer);
			if ($processedClass != null)
			{
				$processedClasses[] = $processedClass;
			}
		}
		return implode(' ', $processedClasses);
	}

	/**
	 * Returns a validated class name that has been made unique to the content being rendered.
	 *
	 * A valid class name contains only letters, numbers, dashes and underscores.
	 * @param string $class The class name to process.
	 * @param mixed $renderer The renderer in use.
	 * @return mixed The processed class name if valid, null otherwise
	 */
	public static function processClassName(string $class, $renderer)
	{
		// Allow only alphanumeric names with underscores.
		if (empty($class) || preg_match("#\W#", $class))
		{
			return null;
		}
		if (Utils::supportsBBCodePlus($renderer))
		{
			return $renderer->getCurrentPrefix() . $class;
		}
		return $class;
	}

	/**
	 * Processes a block of CSS.
	 *
	 * Proxies CSS-included images if image proxying is enabled.
	 * @param string $css The CSS to process.
	 * @param array $options The processing options to be used.
	 * @param mixed $renderer The renderer currently in use.
	 * @param boolean $noBraces Whether to strip curly braces.
	 * @return string The processed CSS.
	 */
	public static function processCss(string $css, array $options, $renderer, bool $noBraces = false)
	{
        // Replace the post id key
        if (Utils::supportsBBCodePlus($renderer))
        {
            $css = str_replace('{post_id}', $renderer->getCurrentPrefix(), $css);
        }

		// Remove curly braces if requested.
		if ($noBraces)
		{
			$css = str_replace('{', '', str_replace('}', '', $css));
		}

		// Only proxy if we are rendering to Html
		if (empty($options['noProxy']) && Utils::supportsBBCodePlus($renderer))
		{
			return preg_replace_callback(self::URL, function ($matches) use($renderer) {
				return $matches[1] . $renderer->getProxiedImageIfActive($matches[2]) . $matches[3];
				}, htmlspecialchars($css));
		}

		// Otherwise, don't bother performing any replacements, since they won't be in important areas.
		return htmlspecialchars($css);
	}

	public static function supportsBBCodePlus($renderer)
    {
        return method_exists($renderer, "supportsBBCodePlus") && $renderer->supportsBBCodePlus();
    }
}