<?php

namespace Accordion\BbCode;

use DOMDocument;
use XF\BbCode\Renderer\AbstractRenderer;

class Accordion
{
	private const DEFAULT_BLOCK_ALIGN = 'bleft';
	private const DEFAULT_SLIDES_TITLES_ALIGN = 'left';
	private const DEFAULT_WIDTH = 650;
	private const DEFAULT_WIDTH_UNIT = 'px';
	private const MAX_WIDTH = 800;
	private const SLIDES_MAX_HEIGHT = 300;

	public static function renderAccordionTag($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		// Get the options and content
		$app = \XF::app();
		$bbCodeContainer = $app->bbCode();
		$options = explode('|', $renderer->renderString($tagOption, array_merge($options, array(
			'stopLineBreakConversion' => true,
			'stopSmilies' => true
		))));
		array_unshift($options, 'killMe');
		unset($options[0]);
		$parser = $bbCodeContainer->parser();
		$rules = $bbCodeContainer->rules(null);
		$content = $renderer->render($renderer->renderSubTreePlain($tagChildren), $parser, $rules, $options);

		// Set the default properties
		$blockAlign = self::DEFAULT_BLOCK_ALIGN;
		$globalHeight = false;
		$width = self::DEFAULT_WIDTH;
		$widthType = self::DEFAULT_WIDTH_UNIT;

		foreach ($options as $option)
		{
			$option = trim($option);

			if (preg_match('#^\d+(px)?$#', $option))
			{
				$width = str_replace(array('px', '%'), '', $option);
				$widthType = 'px';
			}
			elseif (preg_match('#^\d+%$#', $option))
			{
				$width = str_replace(array('px', '%'), '', $option);
				$widthType = '%';
			}
			elseif (preg_match('#^(\d+?(px)?)x(\d+?)$#', $option, $matches))
			{
				$width = str_replace(array('px', '%'), '', $matches[1]);
				$widthType = 'px';
				$globalHeight = str_replace(array('px', '%'), '', $matches[3]);
			}
			elseif (preg_match('#^(\d+?%)x(\d+?)$#', $option, $matches))
			{
				$width = str_replace(array('px', '%'), '', $matches[1]);
				$widthType = '%';
				$globalHeight = str_replace(array('px', '%'), '', $matches[2]);
			}
			elseif ($option == 'bleft')
			{
				$blockAlign = 'bleft';
			}
			elseif ($option == 'bcenter')
			{
				$blockAlign= 'bcenter';
			}
			elseif ($option == 'bright')
			{
				$blockAlign = 'bright';
			}
			elseif ($option == 'fleft')
			{
				$blockAlign = 'fleft';
			}
			elseif ($option == 'fright')
			{
				$blockAlign = 'fright';
			}
		}

		if ($widthType == '%' && $width > 100)
		{
			$width = 100;
		}

		if (!preg_match('#^\d{2,3}$#', $width) || ($widthType == 'px' && $width > self::MAX_WIDTH))
		{
			$width = self::DEFAULT_WIDTH;
			$widthType = self::DEFAULT_WIDTH_UNIT;
		}

		if ($globalHeight !== false && $globalHeight > self::SLIDES_MAX_HEIGHT)
		{
			$globalHeight = self::SLIDES_MAX_HEIGHT;
		}

		$wip = self::getSpecialTags($content);

		$slides = array();
		foreach ($wip as $slide)
		{
			$slide_content = $slide['content'];
			$slide_attributes = $slide['option'];
			$height = $globalHeight;

			// Default Slave Options
			$align = self::DEFAULT_SLIDES_TITLES_ALIGN;
			$title = '';
			$open = '';
			$class_open = '';

			if ($slide_attributes)
			{
				$slideOptions = explode('|', $slide_attributes);

				foreach ($slideOptions as $slideOption)
				{
					$original = $slideOption;
					$slideOption = trim($slideOption);

					if (preg_match('#^\d+$#', $slideOption))
					{
						$height = $slideOption;
					}
					elseif ($slideOption == 'left')
					{
						$align = 'left';
					}
					elseif ($slideOption == 'center')
					{
						$align = 'center';
					}
					elseif ($slideOption == 'right')
					{
						$align = 'right';
					}
					elseif ($slideOption == 'open')
					{
						$open = ' accordion-slide-open';
						$class_open = ' accordion-slide-active';
					}
					elseif (!empty($slideOption))
					{
						$title = $original;
					}
				}
			}

			if ($height !== false && $height < 22)
			{
				$height = 22; // Min-height must be 22px to make overflow scroller visible
			}

			if ($height !== false && $height > self::SLIDES_MAX_HEIGHT)
			{
				$height = self::SLIDES_MAX_HEIGHT;
			}

			// Add slide to slides array
			$slides[] = array(
				'height' => $height,
				'content' => $slide_content,
				'align' => $align,
				'title' => $title,
				'open' => $open,
				'class_open' => $class_open
			);
		}

		return $app->templater()->renderTemplate('public:accordion', array(
			'width' => $width,
			'widthType' => $widthType,
			'blockAlign' => $blockAlign,
			'slides' => $slides
		));
	}

	private static function getSpecialTags($content, array $tags = array('slide'), $getContentBetweenTags = false)
	{
		$tags = implode('|', $tags);
		$afterTagBlank = '[\s]|&\#8203;';

		$count = preg_match_all(
			'#(?P<beforeTag>(?:<[^>]+?>)+)?{(?P<tag>'.$tags.')(=(?P<option>\[([\w\d]+)(?:=.+?)?\].+?\[\/\5\]|[^{}]+)+?)?}'.
			'(?P<afterTag>(?:(?:'.$afterTagBlank.')*<\/[^>]+?>(?:'.$afterTagBlank.')*)+)?(?P<content>.*?)'.
			'{\/\2}(?!(?:\W+)?{\/\2})(?P<outside>.*?)(?={(?:'.$tags.')(?:=[^}]*)?}|$)#msi',
			$content,
			$matches,
			PREG_SET_ORDER
		);

		// Prevent html breaks
		for(; $count > 0; $count--)
		{
			$k = $count-1;
			$content = &$matches[$k]['content'];

			// Restore in a clean way a messy code between normal & special tags
			if (!empty($matches[$k]['beforeTag']) && !empty($matches[$k]['afterTag']))
			{
				$htmlBeforeTagData = explode('>', $matches[$k]['beforeTag']);
				$htmlBeforeTagData = array_filter($htmlBeforeTagData);
				$htmlBeforeTagData = array_values($htmlBeforeTagData);

				$htmlAfterTagData = explode('>', $matches[$k]['afterTag']);
				$htmlAfterTagData = array_filter($htmlAfterTagData);
				$htmlAfterTagData = array_reverse($htmlAfterTagData);

				$catchupContent = '';

				for($i = 0; ; $i++)
				{
					if (!isset($htmlBeforeTagData[$i], $htmlAfterTagData[$i]))
					{
						break;
					}

					$htmlBeforeTag = null;
					$htmlBeforeTagLimitPos = strpos($htmlBeforeTagData[$i], ' ');
					if ($htmlBeforeTagLimitPos !== false)
					{
						$htmlBeforeTag = substr($htmlBeforeTagData[$i], 1, $htmlBeforeTagLimitPos-1);
					}
					else
					{
						$htmlBeforeTag = substr($htmlBeforeTagData[$i], -1); //ie: b>
					}

					$htmlAfterTag = null;
					$htmlAfterTagStartPos = strpos($htmlAfterTagData[$i], '</');
					if ($htmlAfterTagStartPos !== false)
					{
						$htmlAfterTag = substr($htmlAfterTagData[$i], $htmlAfterTagStartPos+2, strlen($htmlAfterTagData[$i]));
					}
					else
					{
						break;
					}

					if ($htmlBeforeTag == $htmlAfterTag)
					{
						$catchupContent .= $htmlBeforeTagData[$i].">";
					}
				}

				if ($catchupContent)
				{
					$content = $catchupContent.$content;
				}
			}

			// Between special tags management
			$extraData = $matches[$k]['outside'];
			$extraDataCheck = str_replace('<br />', '', $extraData);
			$extraDataCheck = trim($extraDataCheck);

			if (empty($extraDataCheck))
			{
				continue;
			}

			if (!$getContentBetweenTags)
			{
				$extraData = $extraDataCheck;
				$contentToErase = preg_split('#[\s]*<?[^>]+?>[\s]*#', $extraData);

				foreach($contentToErase as $text)
				{
					if ($text)
					{
						$extraData = str_replace($text, '', $extraData);
					}
				}
			}

			$content .= $extraData;
			$content = self::_tidyHTML($content);
		}

		return $matches;
	}

	private static function _tidyHTML($html)
	{
		if (!$html)
		{
			return $html;
		}

		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$readyContent = self::_beforeLoadHtml($html);
		$doc->loadHTML('<?xml encoding="utf-8"?>' . $readyContent);
		libxml_clear_errors();
		$doc->encoding = 'utf-8';
		$doc->formatOutput = true;

		$doc->removeChild($doc->firstChild); // remove html tag
		$doc->removeChild($doc->firstChild); // remove xml fix
		$doc->replaceChild($doc->firstChild->firstChild->firstChild, $doc->firstChild); // make wip tag content as first child

		$html = $doc->saveHTML($doc->documentElement);
		$html = self::_afterSaveHtml($html);
		return $html;
	}

	private static function _beforeLoadHtml($html)
	{
		$html = "<wip>{$html}</wip>";
		$html = self::_fixNpTagsRegex($html);
		return $html;
	}

	private static function _afterSaveHtml($html)
	{
		$html = self::_fixNpTagsRegex($html, true);
		$html = preg_replace('#^\s*<wip>(.*)</wip>\s*$#si', '$1', $html);
		return $html;
	}

	private static function _fixNpTagsRegex($html, $revertMode = false)
	{
		if (!$revertMode)
		{
			return preg_replace('#<(/?)(\w+):(\w+)( [^>]+)?>#i', '<$1$2-npfix-$3$4>', $html);
		}

		return preg_replace('#<(/?)(\w+)-npfix-(\w+)( [^>]+)?>#i', '<$1$2:$3$4>', $html);
	}
}