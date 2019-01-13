<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

/**
 * Class Conversation
 * 
 * Extends \XF\Pub\Controller\Conversation
 *
 * @package TickTackk\SignatureOnce\XF\Pub\Controller
 */
class Conversation extends XFCP_Conversation
{
    /**
     * @param ParameterBag $params
     *
     * @return View
     */
    public function actionView(ParameterBag $params)
    {
        $response = parent::actionView($params);

        if ($response instanceof View && $messages = $response->getParam('messages'))
        {
            /** @var \TickTackk\SignatureOnce\XF\Repository\ConversationMessage $conversationMessageRepo */
            $conversationMessageRepo = $this->repository('XF:ConversationMessage');
            /** @noinspection PhpUndefinedFieldInspection */
            $page = $this->filterPage($params->page);
            $messages = $conversationMessageRepo->setConversationsShowSignature($messages, $page);
            $response->setParam('messages', $messages);
        }

        return $response;
    }
}