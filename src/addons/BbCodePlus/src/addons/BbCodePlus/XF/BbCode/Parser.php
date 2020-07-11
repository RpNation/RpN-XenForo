<?php

namespace BbCodePlus\XF\BbCode;

use XF\BbCode\RuleSet;

class Parser extends XFCP_Parser
{
    public function parse($text, RuleSet $ruleSet)
    {
        $this->ruleSet = $ruleSet;

        $this->ast = [];
        $this->astReference =& $this->ast;
        $this->tagStack = [];
        $this->pendingText = '';
        $this->plainTag = null;
        $this->depth = 0;

        $position = 0;
        $length = strlen($text);

        while (preg_match(
            '#(?:\[([a-z0-9_]+)(=|\s+|\])|\[\/([a-z0-9_]+)])#i',
            $text, $match, PREG_OFFSET_CAPTURE, $position
        ))
        {
            if ($match[0][1] > $position)
            {
                // push text
                $plainText = substr($text, $position, $match[0][1] - $position);
                $this->pushText($plainText);
            }

            $fullMatch = $match[0][0];
            $position = $match[0][1] + strlen($fullMatch);

            if (isset($match[3]))
            {
                $this->closeTag($fullMatch, $match[3][0]);
            }
            /*  XF2.1 code, which breaks bbcode parsing for bbcode plus stuff
            else if ($this->plainTag)
            {
                // options below here relate to tag opens, which aren't allowed inside a plain tag, so
                // eat the [ and continue parsing
                $this->pushText($fullMatch[0]);
                $position = $match[0][1] + 1;
            }
            */
            else if ($match[2][0] == ']')
            {
                // simple, optionless tag
                $this->openTag($fullMatch, $match[1][0]);
            }
            /* XF2.1 code, which breaks bbcode parsing for bbcode plus stuff
            else if ($match[2][0] == ' ')
            {
                // complex tag gen 2 - [tag attr=val attr2=val attr3="val"]

                $startPos = $position;
                $_options = [];

                while (preg_match('/\G\s*(\w+)=(?:(("|\')(.*)\3)|([^"\'\s\]]+))(?= |\])/iU', $text, $optionMatches, PREG_OFFSET_CAPTURE, $startPos))
                {
                    $optionKey = strtolower($optionMatches[1][0]);

                    $_options[$optionKey] = isset($optionMatches[5]) ? $optionMatches[5][0] : $optionMatches[4][0];

                    $startPos += strlen($optionMatches[0][0]);
                }

                $endChar = substr($text, $startPos, 1);

                if ($_options && $endChar == ']')
                {
                    $this->openTag(
                        substr($text, $match[0][1], $startPos + 1 - $match[0][1]),
                        $match[1][0],
                        $_options
                    );

                    $position = $startPos + 1;
                }
                else
                {
                    // no options or options don't end with a ] so invalid tag, just skip the initial match part
                    $this->pushText($fullMatch);
                }
            }
            */
            else
            {
                // complex tag - [tag=attributevalue]
                if ($position >= $length)
                {
                    $this->pushText($fullMatch);
                }
                else
                {
                    $delim = substr($text, $position, 1);
                    if ($delim == '"' || $delim == "'")
                    {
                        $startPos = $position + 1;
                        $endPos = strpos($text, "$delim]", $startPos);
                        $startMatch = $delim;
                        $endMatch = "$delim]";
                    }
                    else
                    {
                        $startPos = $position;
                        $endPos = strpos($text, ']', $startPos);
                        $startMatch = '';
                        $endMatch = ']';
                    }
                    if ($endPos)
                    {
                        // Assume it is a simple option initially.
                        $optionStr = substr($text, $startPos, $endPos - $startPos);
                        if ($match[2][0] == '=')
                        {
                            $option = $optionStr;
                        }
                        else
                        {
                            // Treat it as multiple options instead, unless it's not supposed to have multiple options
                            if (!$ruleSet->supportsMultipleOptions($match[1][0]))
                            {
                                $this->pushText($fullMatch);
                                continue;
                            }
                            $optionMatches = array();
                            preg_match_all('#(?:^|\s+)(\w+)=(?:"((?:[^"\\\\]|\\\\.)*)"|(\S+))#is', $optionStr, $optionMatches);
                            $option = array();
                            for ($i = 0; $i < count($optionMatches[1]); $i++)
                            {
                                // Get the option name
                                $name = $optionMatches[1][$i];

                                // Get either a quoted string or a simple string
                                if (!empty($optionMatches[2][$i]))
                                {
                                    // Little hack: add slashes for single quotes so that stripslashes will properly
                                    // strip all slashes (since it removes them from ', " and \).
                                    $value = stripslashes(addcslashes($optionMatches[2][$i], "'"));
                                }
                                else
                                {
                                    $value = $optionMatches[3][$i];
                                }

                                $option[$name] = $value;
                            }

                            if (empty($option))
                            {
                                $this->pushText($fullMatch);
                                continue;
                            }
                        }
                        $this->openTag(
                            $fullMatch . $startMatch . $optionStr . $endMatch,
                            $match[1][0], $option
                        );

                        $position = $endPos + strlen($endMatch);
                    }
                    else
                    {
                        $this->pushText($fullMatch);
                    }
                }
            }
        }

        if ($position < $length)
        {
            $this->pushText(substr($text, $position));
        }

        $this->finalizeText();

        $ast = $this->ast;
        $this->ast = [];

        $null = null;
        $this->astReference = &$null;

        return $ast;
    }
}