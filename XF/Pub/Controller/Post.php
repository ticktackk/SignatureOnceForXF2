<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use TickTackk\SignatureOnce\ControllerPlugin\SignatureOnce as SignatureOnceControllerPlugin;
use XF\ControllerPlugin\AbstractPlugin as AbstractControllerPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\Reroute as RerouteReply;
use XF\Mvc\Reply\Message as MessageReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Error as ErrorReply;

class Post extends XFCP_Post
{
    /**
     * @return ErrorReply|RedirectReply|ViewReply
     */
    public function actionEdit(ParameterBag $parameterBag)
    {
        $reply = parent::actionEdit($parameterBag);

        $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
        $signatureOnceControllerPlugin->setShowSignature(
            $reply,
            'thread',
            'post',
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