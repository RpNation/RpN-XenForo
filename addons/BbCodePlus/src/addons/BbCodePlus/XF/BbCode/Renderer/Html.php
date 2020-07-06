<?php

namespace BbCodePlus\XF\BbCode\Renderer;

use BbCodePlus\BbCode\Utils;
use XF\BbCode\Parser;
use XF\BbCode\RuleSet;

class Html extends XFCP_Html
{
    protected $animations = null;

	protected $classes = null;

	protected $scripts = null;

	private static $numIds = 0;

	private const CLASS_STATES = ['active', 'focus', 'hover'];

	private const EVENTS = ['init', 'click', 'change', 'dblclick', 'mouseenter', 'mouseleave', 'scroll'];

	private $currentPrefix;

	private $topmost = true;

	public function supportsBBCodePlus()
    {
        return true;
    }

    /**
     * Renders a piece of content while keeping track of user classes and scripts.
     *
     * @param string $string The BbCode to render.
     * @param Parser $parser The parser to use.
     * @param RuleSet $rules The BbCode rules.
     * @param array $options The parsing options.
     * @return mixed The rendered HTML.
     * @throws \Exception
     */
	public function render($string, Parser $parser, RuleSet $rules, array $options = [])
	{
		// Check if this is the top-level
		// This helps avoid issues if the render function is called from below, by a user BBCode for example
		if (!$this->topmost)
		{
			// Do not attempt any CSS or JS rendering, just render the page.
			return parent::render($string, $parser, $rules, $options);
		}

		// Re-initialize the renderer's state
        $this->animations = array();
		$this->classes = array();
		$this->scripts = array();
		$id = self::generateUniqueId();
		$this->currentPrefix = "p$id-";

		// Render the BBCode, add the styles and the scripts.
		$this->topmost = false;
		$render = parent::render($string, $parser, $rules, $options);
		$this->topmost = true;

		$style = array();
		foreach ($this->animations as $name => $body)
        {
            $style[] = "@keyframes $name { $body } ";
        }
		foreach ($this->classes as $name => $states)
		{
			foreach ($states as $state => $bodies)
			{
			    foreach ($bodies as $body)
                {
                    $css = $body['css'];
                    $minWidth = $body['minWidth'];
                    $maxWidth = $body['maxWidth'];
                    $queryStart = '';
                    $queryEnd = '';
                    if ($minWidth != null || $maxWidth != null)
                    {
                        $queryStart = '@media only screen';
                        if ($minWidth != null)
                        {
                            $queryStart .= " and (min-width: $minWidth)";
                        }
                        if ($maxWidth != null)
                        {
                            $queryStart .= " and (max-width: $maxWidth)";
                        }
                        $queryStart .= '{ ';
                        $queryEnd .= ' }';
                    }
                    $style[] = "$queryStart.${name}${state} {{$css}}$queryEnd";
                }
			}
		}
		$this->templater->inlineCss(implode('', $style));

		$script = [];
		foreach ($this->scripts as $class => $events)
		{
			foreach ($events as $event => $body)
			{
				$script[] = "registerBbScript('$class','$event',function(){try{id='$id';_this=this;" . $body . "}catch(e){console.log(e);}});";
			}
		}
		$this->templater->inlineJs(str_replace('</script>', '</\' + \'script>', implode('', $script)));

		// De-initialize the renderer's state
        $this->animations = null;
		$this->classes = null;
		$this->scripts = null;
		$this->currentPrefix = null;

		return $render;
	}

    public function renderTagUser(array $children, $option, array $tag, array $options)
    {
        $content = $this->renderSubTree($children, $options);
        if ($content === '')
        {
            return '';
        }

        $userId = intval($option);
        if ($userId <= 0)
        {
            return $content;
        }

        $link = \XF::app()->router('public')->buildLink('full:members', ['user_id' => $userId]);

        $username = ltrim(self::getTagContents($children, $options), '@');
        return $this->wrapHtml(
            '<a href="' . htmlspecialchars($link) . '" class="username" data-xf-init="member-tooltip" data-user-id="' . $userId .  '" data-username="' . $username . '">',
            $content,
            '</a>'
        );
    }

    private function getTagContents(array $children, $options)
    {
        $output = '';
        foreach ($children AS $element)
        {
            if (is_array($element))
            {
                $output .= self::getTagContents($element['children'], $options);
            }
            else
            {
                $output .= $this->renderString($element, $options);
            }
        }

        return $output;
    }

	/**
	 * Proxies an image through the site's proxy system.
	 *
	 * @param string $url The image url to proxy.
	 * @return string The proxied image URL.
	 */
	public function getProxiedImageIfActive(string $url)
	{
		$proxiedUrl = $this->formatter->getProxiedUrlIfActive('image', $url);
		return $proxiedUrl != null ? $proxiedUrl : $url;
	}

	/**
	 * Returns the current prefix in use for the BbCode being processed.
	 *
	 * @return string A string prefix if currently rendering some content; null otherwise.
	 */
	public function getCurrentPrefix()
	{
		return $this->currentPrefix;
	}

	public function addAnimation(string $name, array $keyframes)
    {
        // Get its unique name
        $processedName = Utils::processClassName($name, $this);
        if ($processedName == null)
        {
            return;
        }

        // Build the animation's definition
        $definition = '';
        foreach ($keyframes as $percentage => $body)
        {
            $css = Utils::processCss($body, [], $this, true);
            $definition .= "$percentage% { $css } ";
        }

        // Store the animation
        $this->animations[$processedName] = $definition;
    }

	/**
	 * Adds a class styling rule to the page.
	 *
	 * @param string $name The name of the class.
	 * @param string $body The class's attributes.
	 * @param string $state The element's state selector (active, hover, etc.).
     * @param string $minWidth The class's optional min-width media query.
     * @param string $maxWidth The class's optional max-width media query.
	 */
	public function addClass(
	    string $name,
        string $body,
        string $state = null,
        string $minWidth = null,
        string $maxWidth = null
    ) {
		// Get its unique name
		$processedName = Utils::processClassName($name, $this);
		if ($processedName == null || ($state != null && !in_array($state, self::CLASS_STATES)))
		{
			return;
		}

		// Store the class
        $stateKey = $state == null ? '' : ':' . $state;
		if (!array_key_exists($processedName, $this->classes))
		{
			$this->classes[$processedName] = [];
		}
		if (!array_key_exists($stateKey, $this->classes[$processedName]))
        {
            $this->classes[$processedName][$stateKey] = [];
        }
		$this->classes[$processedName][$stateKey][] = [
		    'css' => Utils::processCss($body, [], $this, true),
            'minWidth' => $minWidth,
            'maxWidth' => $maxWidth
        ];
	}

	/**
	 * Adds a post script to the list of page scripts.
	 *
	 * @param string $class The class to which to apply the script.
	 * @param string $on The event on which to call the script.
	 * @param string $body The contents of the script.
     * @param int $version The version of BBScript to use.
	 * @return array Any eventual errors which occurred during parsing.
	 */
	public function addScript(string $class, string $on, string $body, int $version) {
		// Get its unique name
		$processedClass = Utils::processClassName($class, $this);
		if ($processedClass == null)
		{
			return ["Invalid class name '$class'"];
		}

		// Get the event
		if (!in_array($on, self::EVENTS))
		{
			return ["Invalid event '$on'"];
		}

		// Parse the BBScript and check if there was a parsing error
        $result = null;
        switch ($version) {
            case '1':
                $result = \BbCodePlus\BbScript\Parser::parse($body);
                break;
            case '2':
                $result = \BbCodePlus\BbScript2\Parser::parse($body);
                break;
            default:
                return ["Invalid version"];
        }
        if (!empty($result['errors']))
        {
            return $result['errors'];
        }

		// Store the script
		if (!array_key_exists($processedClass, $this->scripts))
		{
			$this->scripts[$processedClass] = [];
		}
		$this->scripts[$processedClass][$on] = $result['js'];
		return null;
	}

    /**
     * Generates a unique ID for a specific post.
     *
     * This can be prefixed to class names in order to avoid conflicts between posts. A random part is appended to avoid
     * conflicts linked to inline editing of content (which will reset the counter).
     * @return int A unique identifier for the post.
     * @throws \Exception
     */
	private static function generateUniqueId()
	{
		return Html::$numIds++ . "_" . dechex(random_int(0, 1000000000));  // should be good enough
	}
}