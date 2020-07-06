<?php

namespace ModerationTools\XF\Entity;

class ConversationMaster extends XFCP_ConversationMaster
{
    public function canView(&$error = null)
    {
        $visitor = \XF::visitor();
        return parent::canView() || $visitor->canBypassUserPrivacy();
    }
}