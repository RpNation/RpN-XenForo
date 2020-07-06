<?php

namespace ModerationTools\XF\Pub\Controller;

class Conversation extends XFCP_Conversation
{
    protected function assertViewableUserConversation($conversationId, array $extraWith = [])
    {
        $visitor = \XF::visitor();

        /** @var \XF\Finder\ConversationUser $finder */
        $finder = $this->finder('XF:ConversationUser');
        if (!$visitor->canBypassUserPrivacy())
        {
            $finder->forUser($visitor, false);
        }
        $finder->where('conversation_id', $conversationId);
        $finder->with($extraWith);

        /** @var \XF\Entity\ConversationUser $conversation */
        $conversation = $finder->fetchOne();
        if (!$conversation || !$conversation->Master)
        {
            throw $this->exception($this->notFound(\XF::phrase('requested_conversation_not_found')));
        }

        return $conversation;
    }
}