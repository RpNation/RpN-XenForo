<?php

namespace ModerationTools\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\Conversation;

class ConversationOthers extends Conversation
{
    public function actionIndex(ParameterBag $params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->canBypassUserPrivacy())
        {
            return $this->redirect($this->buildLink('conversations'));
        }
        $page = $this->filterPage($params->page);
        $perPage = $this->options()->discussionsPerPage;

        $filters = $this->getConversationFilterInput();

        $conversationRepo = $this->getConversationRepo();

        if ($params->user_id != null)
        {
            /** @var \XF\Repository\User $userRepo */
            $userRepo = self::repository('XF:User');
            $user = $userRepo->getVisitor(intval($params->user_id));
            $conversationFinder = $conversationRepo->findUserConversations($user);
        }
        else
        {
            $conversationFinder = $conversationRepo->finder('XF:ConversationUser')->setDefaultOrder('last_message_date', 'desc');
        }
        $conversationFinder = $conversationFinder->limitByPage($page, $perPage);

        $this->applyConversationFilters($conversationFinder, $filters);

        $totalConversations = $conversationFinder->total();
        $this->assertValidPage($page, $perPage, $totalConversations, 'conversations');

        $userConvs = $conversationFinder->fetch();

        $starterFilter = !empty($filters['starter_id']) ? $this->em()->find('XF:User', $filters['starter_id']) : null;
        $receiverFilter = !empty($filters['receiver_id']) ? $this->em()->find('XF:User', $filters['receiver_id']) : null;

        $viewParams = [
            'userConvs' => $userConvs,

            'page' => $page,
            'perPage' => $perPage,
            'total' => $totalConversations,

            'starterFilter' => $starterFilter,
            'receiverFilter' => $receiverFilter,

            'filters' => $filters
        ];
        return $this->view('XF:Conversations\Listing', 'conversation_list', $viewParams);
    }
}