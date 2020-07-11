<?php

namespace BbCodePlus\BbCode;

use BbCodePlus\XF\BbCode\Renderer\Html;
use BbCodePlus\XF\BbCode\RuleSet;

/**
 * Provides additional BBCode+ tags.
 *
 * @package BbCodePlus\BbCode
 */
class Tags
{
	private const VALID_INPUT_TYPES = ['button', 'password', 'text', 'textarea'];

	private const VALID_WIDTH_PATTERN = '/^\d+[a-z]*$/';

    /**
     * Renders a BBCode+ tag.
     *
     * @param array $tagChildren The keyframes of the animation.
     * @param mixed $tagOption The option (or options) of the class.
     * @param array $tag The name of the tag.
     * @param array $options The renderer options.
     * @param Html $renderer The renderer used to render the tag.
     * @return string The HTML code rendered.
     */
	public static function renderBbCodePlusTag($tagChildren, $tagOption, array $tag, array $options, $renderer)
    {
        $tagName = strtolower($tag['tag']); // Just in case
        if (!Utils::supportsBBCodePlus($renderer) && $tagName != 'div')
        {
            return '';
        }
        if ($tagOption == null)
        {
            $tagOption = [];
        }
        switch ($tagName)
        {
            case 'animation':
                return self::renderAnimationTag($tagChildren, $tagOption, $options, $renderer);
            case 'class':
                return self::renderClassTag($tagChildren, $tagOption, $options, $renderer);
            case 'div':
                return self::renderDivTag($tagChildren, $tagOption, $options, $renderer);
            case 'export':
                return self::renderExportTag($tagChildren, $tagOption, $options, $renderer);
            case 'function':
                return self::renderFunctionTag($tagChildren, $tagOption, $options, $renderer);
            case 'import':
                return self::renderImportTag($tagChildren, $tagOption, $options, $renderer);
            case 'input':
                return self::renderInputTag($tagChildren, $tagOption, $renderer);
            case 'script':
                return self::renderScriptTag($tagChildren, $tagOption, $renderer);
            default:
                return '';
        }
    }

    /**
     * Registers an animation to be added to the page.
     *
     * @param array $tagChildren The keyframes of the animation.
     * @param mixed $tagOption The option (or options) of the class.
     * @param array $options The renderer options.
     * @param Html $renderer The renderer used to render the tag.
     * @return string Nothing.
     */
	public static function renderAnimationTag($tagChildren, $tagOption, array $options, Html $renderer)
    {
        // Get the required entities
        $app = \XF::app();
        $bbCodeContainer = $app->bbCode();
        $parser = $bbCodeContainer->parser();

        // Build the keyframes ruleset
        $keyframesRuleSet = new RuleSet(null, null, false);
        $keyframesRuleSet->addTag('keyframe', [
            'hasOption' => true,
            'optionMatch' => '/^\d+$/',
            'plain' => true,
            'stopSmilies' => true,
            'stopAutoLink' => true
        ]);

        // Get the defined keyframes
        $animationName = $tagOption;
        $content = $renderer->renderSubTree($tagChildren, $options);
        $keyframesAst = $parser->parse($content, $keyframesRuleSet);
        $keyframes = [];
        foreach ($keyframesAst as $node)
        {
            if (!is_array($node) || $node['tag'] != 'keyframe')
            {
                continue;
            }
            $percentage = $node['option'];
            $body = $renderer->renderSubTreePlain($node['children']);
            $keyframes[$percentage] = $body;
        }

        // Save the animation
        $renderer->addAnimation($animationName, $keyframes);

        return "";
    }

	/**
	 * Registers a class to be added to the page.
	 *
	 * @param array $tagChildren The body of the class.
	 * @param mixed $tagOption The option (or options) of the class.
	 * @param array $options The renderer options.
	 * @param Html $renderer The renderer used to render the tag.
	 * @return string Nothing.
	 */
	public static function renderClassTag($tagChildren, $tagOption, array $options, Html $renderer)
	{
		$state = null;
		$minWidth = null;
		$maxWidth = null;
		if (is_array($tagOption))
		{
            $class = $tagOption['name'];
            $state = $tagOption['state'] ?? null;
			if (!empty($tagOption['minWidth']) && preg_match(self::VALID_WIDTH_PATTERN, $tagOption['minWidth']))
			{
                $minWidth = $tagOption['minWidth'];
            }
            if (!empty($tagOption['maxWidth']) && preg_match(self::VALID_WIDTH_PATTERN, $tagOption['maxWidth']))
            {
                $maxWidth = $tagOption['maxWidth'];
            }
		}
		else
		{
			$class = $tagOption;
		}
		$content = $renderer->renderSubTree($tagChildren, $options);
		if ($class != null)
		{
			$renderer->addClass($class, $content, $state, $minWidth, $maxWidth);
		}
		return "";
	}

    public static function renderExportTag(/** @noinspection PhpUnusedParameterInspection */ $tagChildren, $tagOption, array $options, Html $renderer)
    {
        $content = $renderer->renderSubTree($tagChildren, $options);
        return $content;
    }

    public static function renderImportTag(/** @noinspection PhpUnusedParameterInspection */ $tagChildren, $tagOption, array $options, Html $renderer)
    {
        // Try to determine the author of the current post
        // If there is no entity available, it's probably a new post being written
        if (empty($options['entity']) || !($options['entity'] instanceof \XF\Entity\Post))
        {
            $currentPostAuthorId = \XF::visitor()->user_id;
        }
        else
        {
            $currentPostAuthorId = $options['entity']->user_id;
        }

        // Get the required entities
        $app = \XF::app();
        $bbCodeContainer = $app->bbCode();
        $parser = $bbCodeContainer->parser();
        $rules = $bbCodeContainer->rules('post');

        // Get the imported post
        $post_id = intval($renderer->renderSubTree($tagChildren, $options));
        if ($post_id <= 0)
        {
            return "Invalid post ID, must be positive integer";
        }
        $finder = \XF::finder('XF:Post');
        /** @var \XF\Entity\Post $importedPost */
        $importedPost = $finder->where('post_id', $post_id)->fetchOne();
        if ($importedPost == null
            || !$importedPost->canView() && $currentPostAuthorId != $importedPost->user_id
            || $importedPost->message_state != 'visible'
            || $importedPost->Thread->discussion_state != 'visible'
            )
        {
            return "Post #" . $post_id . " could not be imported";
        }

        // Render the exported parts of the imported content
        preg_match_all("#\[export\](.*?)\[/export\]#is", $importedPost->message, $matches);
        $exportedContent = implode('', $matches[1]);
        return $renderer->renderSubTree($parser->parse($exportedContent, $rules), $options);
    }

    public static function renderFunctionTag($tagChildren, $tagOption, array $options, Html $renderer)
    {
        // TODO
        // Get the required entities
        $app = \XF::app();
        $bbCodeContainer = $app->bbCode();
        $parser = $bbCodeContainer->parser();

        // Build the keyframes ruleset
        $functionRuleSet = new RuleSet(null, null, false);
        $functionRuleSet->addTag('param', [
            'hasOption' => true,
            'optionMatch' => '/^[a-zA-Z_]+$/',
            'plain' => true,
            'stopSmilies' => true,
            'stopAutoLink' => true
        ]);
        $functionRuleSet->addTag('body', [
            'hasOption' => false,
            'plain' => true,
            'stopSmilies' => true,
            'stopAutoLink' => true
        ]);

        // Get the function definition
        $functionName = $tagOption;
        $content = $renderer->renderSubTree($tagChildren, $options);
        $functionAst = $parser->parse($content, $functionRuleSet);
        $params = [];
        $body = null;
        foreach ($functionAst as $node)
        {
            if (!is_array($node) || ($node['tag'] != 'body' && $node['tag'] != 'param'))
            {
                continue;
            }
            switch ($node['tag'])
            {
                case 'body':
                    $body = $renderer->renderSubTreePlain($node['children']);
                    break;
                case 'param':
                    $params[$node['option']] = $renderer->renderSubTreePlain($node['children']);
                    break;
                default:
                    break;
            }
        }

        return "";
    }

	/**
	 * Registers a script to be added to the page.
	 *
	 * @param array $tagChildren The body of the script.
	 * @param mixed $tagOption The option (or options) of the script.
	 * @param Html $renderer The renderer used to render the tag.
	 * @return string Nothing if successful, a list of errors otherwise.
	 */
	public static function renderScriptTag($tagChildren, $tagOption, Html $renderer)
	{
		if (is_array($tagOption))
		{
			$class = $tagOption['class'];
            $on = empty($tagOption['on']) ? 'init' : $tagOption['on'];
            $version = empty($tagOption['version']) ? '1' : $tagOption['version'];
		}
		else
		{
			$class = $tagOption;
			$on = 'init';
			$version = '1';
		}
		$body = $renderer->renderSubTreePlain($tagChildren);
		$errors = $renderer->addScript($class, $on, $body, $version);
		if ($errors != null)
		{
			$e = '<ul class="bbscript-errors">';
			foreach ($errors as $error)
			{
				$e .= '<li class="bbscript-error">' . htmlspecialchars($error) . '</li>';
			}

			$e .= '</ul>';
			return $e;
		}

		return "";
	}

	/**
	 * Renders a div tag, which can be either styled or classed.
	 *
	 * @param array $tagChildren The body of the div.
	 * @param mixed $tagOption The option (or options) of the div.
	 * @param array $options The renderer options.
	 * @param mixed $renderer The renderer used to render the tag.
	 * @return string The styled div.
	 */
	public static function renderDivTag($tagChildren, $tagOption, array $options, $renderer)
	{
        $class = null;
		$style = null;
		if (is_array($tagOption))
		{
            $class = Utils::processClassNames($tagOption['class'] ?? null, $renderer);
            $style = $tagOption['style'] ?? null;
		}
		else
		{
			$style = $tagOption;
		}
		$style = $style ? Utils::processCss($style, $options, $renderer) : null;
		$content = $renderer->renderSubTree($tagChildren, $options);

		if (Utils::supportsBBCodePlus($renderer) && $class != null && $style != null)
		{
			return "<div class=\"$class\" style=\"$style\">$content</div>";
		}
		elseif ($style != null)
		{
			return "<div style=\"$style\">$content</div>";
		}
		elseif (Utils::supportsBBCodePlus($renderer) && $class != null)
		{
			return "<div class=\"$class\">$content</div>";
		}

		return "<div>$content</div>";
	}

	/**
	 * Renders an input tag, which can be classed.
	 *
	 * @param array $tagChildren The input's initial value.
	 * @param mixed $tagOption The option (or options) of the input.
	 * @param Html $renderer The renderer used to render the tag.
	 * @return string The input tag.
	 */
	public static function renderInputTag($tagChildren, $tagOption, Html $renderer)
	{
		// Get the options
		$value = " value=\"" . htmlspecialchars($renderer->renderSubTreePlain($tagChildren)) . "\"";
		$type = " type=\"text\"";
		$maxlength = "";
		$placeholder = "";
		if (is_array($tagOption))
		{
			$class = Utils::processClassNames($tagOption['class'], $renderer);
			if (!empty($tagOption['type']) && in_array($tagOption['type'], self::VALID_INPUT_TYPES))
			{
				$type = " type=\"${tagOption['type']}\"";
			}
			if (!empty($tagOption['maxlength']) && is_numeric($tagOption['maxlength']))
			{
				$maxlength = " maxlength=\"${tagOption['maxlength']}\"";
			}
			if (!empty($tagOption['placeholder']))
			{
				$placeholder = " placeholder=\"" . htmlspecialchars($tagOption['placeholder']) . "\"";
			}
		}
		else
		{
			$class = Utils::processClassName($tagOption, $renderer);
		}

		// Check if an invalid name was specified
		if ($class == null)
		{
			return "";
		}

		if (!empty($tagOption['type']) && $tagOption['type'] == 'textarea')
		{
			$value = htmlspecialchars($renderer->renderSubTreePlain($tagChildren));
			return "<textarea class=\"${class}\"${maxlength}${placeholder}>${value}</textarea>";
		}
		return "<input class=\"${class}\"${type}${maxlength}${placeholder}${value} />";
	}
}