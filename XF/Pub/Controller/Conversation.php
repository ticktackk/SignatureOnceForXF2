<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\ControllerPlugin\AbstractPlugin as AbstractControllerPlugin;
use TickTackk\SignatureOnce\ControllerPlugin\SignatureOnce as SignatureOnceControllerPlugin;

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
     * @param ParameterBag $parameterBag
     *
     * @return ViewReply
     */
    public function actionView(ParameterBag $parameterBag)
    {
        $reply = parent::actionView($parameterBag);

        $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
        $signatureOnceControllerPlugin->setShowSignature(
            $reply,
            'conversation',
            'messages',
            'page'
        );

        return $reply;
    }

    /**
     * @param ParameterBag $parameterBag
     *
     * @return ErrorReply|RedirectReply|ViewReply
     */
    public function actionAddReply(ParameterBag $parameterBag)
    {
        $reply = parent::actionAddReply($parameterBag);

        $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
        $signatureOnceControllerPlugin->setShowSignature(
            $reply,
            'conversation',
            'messages',
            null
        );

        return $reply;
    }

    public function actionMessagesEdit(ParameterBag $params)
    {
        $reply = parent::actionMessagesEdit($params);

        $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
        $signatureOnceControllerPlugin->setShowSignature(
            $reply,
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