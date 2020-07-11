<?php

namespace BbCodePlus\XF\BbCode;

class Processor extends XFCP_Processor {

    public function replaceOptionInTagOpen($open, $newValue)
    {
        if (is_array($newValue)) {
            return $open;   // TODO Actually replace filtered words
                            // Only used for censor, though, so not that priority
        } else {
            return parent::replaceOptionInTagOpen($open, $newValue);
        }
    }
}