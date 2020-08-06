<?php

namespace RpNation\BbCode;

class RuleSet extends XFCP_RuleSet
{
    public function addDefaultTags()
    {
        parent::addDefaultTags();

        $this->modifyTag('font', ['supportOptionKeys' => Ruleset::OPTION_KEYS_BOTH]);
    }
}
