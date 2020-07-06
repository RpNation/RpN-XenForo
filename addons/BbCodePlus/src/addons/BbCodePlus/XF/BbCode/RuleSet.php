<?php

namespace BbCodePlus\XF\BbCode;

class RuleSet extends XFCP_RuleSet
{
	public const OPTIONS_NONE = 0;
	public const OPTIONS_SIMPLE = 1;
	public const OPTIONS_MULTIPLE = 2;

    public function addDefaultTags()
    {
        parent::addDefaultTags();

        $this->addTag('user', [
            'hasOption' => true,
            'plain' => false,
            'stopSmilies' => true,
            'stopAutoLink' => true
        ]);
    }

	public function supportsMultipleOptions(string $tag)
    {
        $definition = $this->getTag(strtolower($tag));
        if (!is_array($definition))
        {
            return false;
        }

        if (isset($definition['supportOptionKeys']))
        {
            switch ($definition['supportOptionKeys'])
            {
                case \XF\BbCode\RuleSet::OPTION_KEYS_BOTH:
                case RuleSet::OPTION_KEYS_ONLY:
                    return true;
            }
        }

        return !empty($definition['multipleOptions']);
    }

	public function validateTag($tag, $option = null, &$parsingModifiers = [], array $tagStack = [])
	{
		$parsingModifiers = [];

		$definition = $this->getTag($tag);
		if (!is_array($definition))
		{
		    return false;
		}

        if (!empty($definition['validParents']))
        {
            if (!$tagStack)
            {
                return false;
            }

            $lastTag = $tagStack[0];
            if (!in_array($lastTag['tag'], $definition['validParents']))
            {
                return false;
            }
        }

        if (!$this->validateOption($option, $definition))
        {
            return false;
        }

        if ($option !== null && !empty($definition['optionMatch']) && is_scalar($option))
        {
            if (!preg_match($definition['optionMatch'], $option))
            {
                return false;
            }
        }

		$parsingModifiers = $definition;

		if (isset($definition['parseValidate']))
		{
			$validated = call_user_func($definition['parseValidate'], $tag, $option, $tagStack);
			if ($validated === false)
			{
				return false;
			}

			if (is_array($validated))
			{
				$parsingModifiers = array_merge($parsingModifiers, $validated);
			}
		}

		// Check if this tag supports multiple options and if the required options are present.
		// If a simple option is supported and there is either only one required option or a default option specified,
		// the value of the simple option will be used as the value of that required/default option.
		if (!empty($definition['multipleOptions']) && $option != null)
		{
			$optionDefinitions = $definition['multipleOptions']['options'];

			// Ensure the user has either provided multiple options, or that there is one simple option and only one
			// required option.
			if (!is_array($option))
			{
				if (isset($definition['hasOption']) && $definition['hasOption'])
				{
					// Try to get the default option
					if (!empty($definition['multipleOptions']['default']) &&
						array_key_exists($definition['multipleOptions']['default'], $optionDefinitions))
					{
						return true;
					}

					// Try to get a single required option
					$requiredOption = null;
					foreach ($optionDefinitions as $name => $required)
					{
						if ($required)
						{
							if ($requiredOption != null)
							{
								return false;
							}
							$requiredOption = $name;
						}
					}

					if ($requiredOption == null)
					{
						return false;
					}

					// There is only one required option, and a simple option was provided, so this tag is valid.
					return true;
				}
				return false;
			}

			// Check if every required option is present.
			foreach ($optionDefinitions as $name => $required)
			{
				if ($required && (!array_key_exists($name, $option)))
				{
					return false;
				}
			}

			// Check if every option present is a valid one.
			foreach ($option as $name => $value)
			{
				if (!array_key_exists($name, $optionDefinitions))
				{
					return false;
				}
			}

		}

		return true;
	}
}