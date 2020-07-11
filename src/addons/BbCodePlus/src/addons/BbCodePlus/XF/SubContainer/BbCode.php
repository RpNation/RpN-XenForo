<?php

namespace BbCodePlus\XF\SubContainer;

use XF\BbCode\RuleSet;
use XF\Container;

class BbCode extends XFCP_BbCode
{
    public function initialize()
    {
        parent::initialize();

        $this->container->set('processor', function(Container $c)
        {
            $class = $this->extendClass('XF\BbCode\Processor');
            return new $class();
        }, false);
    }

    public function rules($context)
    {
        $ruleSet = parent::rules($context);
        $ruleSet->modifyTag('div', [
			'supportOptionKeys' => RuleSet::OPTION_KEYS_BOTH,
            'multipleOptions' => [
                'default' => 'style',
                'options' => [
                    'class' => false,
                    'style' => false
                ]
            ]
        ]);
        $ruleSet->modifyTag('class', [
			'supportOptionKeys' => RuleSet::OPTION_KEYS_BOTH,
            'multipleOptions' => [
                'default' => 'name',
                'options' => [
                    'name' => true,
                    'state' => false,
                    'minWidth' => false,
                    'maxWidth' => false
                ]
            ]
        ]);
        $ruleSet->modifyTag('script', [
			'supportOptionKeys' => RuleSet::OPTION_KEYS_BOTH,
            'multipleOptions' => [
                'default' => 'class',
                'options' => [
                    'class' => true,
                    'on' => false,
                    'version' => false
                ]
            ]
        ]);
        $ruleSet->modifyTag('input', [
			'supportOptionKeys' => RuleSet::OPTION_KEYS_BOTH,
            'multipleOptions' => [
                'default' => 'class',
                    'options' => [
                    'class' => true,
                    'type' => false,
                    'maxlength' => false,
                    'placeholder' => false
                ]
            ]
        ]);
        return $ruleSet;
    }
}