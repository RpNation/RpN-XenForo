<?php

namespace RpNation\BbCode;

class RuleSet extends XFCP_RuleSet
{
    public function addDefaultTags()
    {
        parent::addDefaultTags();

        $this->tags['font']['supportOptionKeys'] = RuleSet::OPTION_KEYS_BOTH;
    }
}