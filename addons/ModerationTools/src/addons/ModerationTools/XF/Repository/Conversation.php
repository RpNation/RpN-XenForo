<?php

namespace ModerationTools\XF\Repository;

class Conversation extends XFCP_Conversation
{
    public function markUserConversationRead(\XF\Entity\ConversationUser $userConv, $newRead = null)
    {
        $visitor = \XF::visitor();
        if ($userConv->User->user_id != $visitor->user_id)
        {
            return;  // Don't allow moderators to mark a conversation as read
        }
        parent::markUserConversationRead($userConv, $newRead);
    }
}