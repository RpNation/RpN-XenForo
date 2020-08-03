<?php

namespace RpNation\BbCode;

use XF;
use XF\BbCode\Renderer\AbstractRenderer;

class Tabs
{
	static $uniqueContainer = -1;

	static $uniqueTab = -1;

	public function __construct()
	{
		if (self::$uniqueContainer == -1 || self::$uniqueTab == -1)
		{
			self::$uniqueContainer = XF::$time;
			self::$uniqueTab = XF::$time;
		}
	}

	public static function renderTabsCode($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$tabs = new Tabs();
		return $tabs->_renderTabsCode($tagChildren, $tagOption, $tag, $options, $renderer);
	}

	public function _renderTabsCode($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$text = $renderer->renderSubTree($tagChildren, $options);
		$tabs = $this->getTabs($text);
		$tabs = $this->filterEmptyTabs($tabs);

		if (empty($tabs))
		{
			return '';
		}

		$tabHead = '';
		$tabContent = '';
		$first = true;
		$containerId = 'tabContainer_' . (self::$uniqueContainer++);
		foreach ($tabs as $tab)
		{
			$active = $first ? "active" : "";
			$style = $first ? "display: list-item;" : "display: none;";
			$first = false;
			$tabHead .= "<li class=\"bbTab $active\"><a data-xf-click=\"bbTab\" data-tab-id=\"${tab['id']}\" data-tab-container-id=\"$containerId\" href=\"javascript:\">${tab['title']}</a></li>";
			$tabContent .= "<li class=\"bbTabContent\" id=\"${tab['id']}\" style=\"$style\">${tab['content']}</li>";
		}

		$ret = "
    			<div class=\"tabsBb\" id=\"{$containerId}\" >
	    			<ul class=\"tabsBbTitles mainTabsBb\">{$tabHead}</ul>
	    			<ul class=\"tabsBbContent\">{$tabContent}</ul>
    			</div>
    	";
		return $ret;
	}

	public static function renderSingleTabCode($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$tab = new Tabs();
		return $tab->_renderSingleTabCode($tagChildren, $tagOption, $tag, $options, $renderer);
	}

	public function _renderSingleTabCode($tagChildren, $tagOption, $tag, array $options, AbstractRenderer $renderer)
	{
		$title = $tagOption;
		$title = !empty($title) ? $title : 'Tab';
		return "[tab={$title}]" . trim($renderer->renderSubTree($tag['children'], $options)) . "[/tab]";
	}

	private function getTabs($text)
	{
		$text = trim($text);
		if (empty($text))
		{
			return array();
		}

		preg_match_all("@\[tab([^]]*)\](.*)\[/tab\]@isU", $text, $matches);
		$totalTabs = !empty($matches) ? count($matches[0]) : 0;
		if ($totalTabs == 0 && !empty($text))
		{
			$singleTab = array(
				'title' => 'Tab',
				'content' => $text,
				'id' => 'tab_' . (self::$uniqueTab++)
			);
			return array($singleTab);
		}

		$tabs = array();
		for ($i = 0; $i < $totalTabs; $i++)
		{
			$title = $matches[1][$i];
			if (substr($title, 0, 1) == '=')
			{
				$title = substr($title, 1);
			}

			$tabs[] = array(
				'title' => !empty($title) ? $title : 'Tab',
				'content' => $matches[2][$i],
				'id' => 'tab_' . (self::$uniqueTab++)
			);
		}
		return $tabs;
	}

	private function filterEmptyTabs($tabs)
	{
		$ret = array();
		foreach ($tabs as $tab)
		{
			$realContent = trim(strip_tags($tab['content']));
			if (!empty($realContent) || preg_match("/<img/", $tab['content']))
			{
				$ret[] = $tab;
			}
		}
		return $ret;
	}
}