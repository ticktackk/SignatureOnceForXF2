<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\ControllerPlugin\AbstractPlugin as AbstractControllerPlugin;
use TickTackk\SignatureOnce\ControllerPlugin\SignatureOnce as SignatureOnceControllerPlugin;

/**
 * @version 2.0.0
 */
class Conversation extends XFCP_Conversation
{
    /**
     * @version 2.0.0
     *
     * @param ParameterBag $params
     *
     * @return ViewReply
     *
     * @throws \Exception
     *
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function actionView(ParameterBag $params)
    {
        $reply = parent::actionView($params);

        $this->getSignatureOnceControllerPlugin()->setContentsFromCurrentPage(
            $reply,
            'conversation_message',
            'conversation',
            'messages'
        );

        return $reply;
    }

    /**
     * @version 2.0.0
     *
     * @param ParameterBag $params
     *
     * @return ErrorReply|RedirectReply|ViewReply
     *
     * @throws \Exception
     */
    public function actionAddReply(ParameterBag $params)
    {
        $reply = parent::actionAddReply($params);

        $this->getSignatureOnceControllerPlugin()->setContentsFromCurrentPage(
            $reply,
            'conversation_message',
            'conversation',
            'messages',
            null
        );

        return $reply;
    }

    /**
     * @version 2.0.0
     *
     * @param ParameterBag $params
     *
     * @return AbstractReply|ErrorReply|RedirectReply|ViewReply
     *
     * @throws \Exception
     */
    public function actionMessagesEdit(ParameterBag $params)
    {
        $reply = parent::actionMessagesEdit($params);

        $this->getSignatureOnceControllerPlugin()->setContentsFromCurrentPage(
            $reply,
            'conversation_message',
            'conversation',
            'message',
            null
        );

        return $reply;
    }

    /**
     * @return AbstractControllerPlugin|SignatureOnceControllerPlugin
     */
    protected function getSignatureOnceControllerPlugin() : SignatureOnceControllerPlugin
    {
        return $this->plugin('TickTackk\SignatureOnce:SignatureOnce');
    }
}