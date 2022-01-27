<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use TickTackk\SignatureOnce\ControllerPlugin\SignatureOnce as SignatureOnceControllerPlugin;
use XF\ControllerPlugin\AbstractPlugin as AbstractControllerPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\Error as ErrorReply;

/**
 * @version 2.0.0 Alpha 1
 */
class Post extends XFCP_Post
{
    /**
     * @version 2.0.0 Alpha 1
     *
     * @param ParameterBag $params
     *
     * @return AbstractReply|ErrorReply|RedirectReply|ViewReply
     */
    public function actionEdit(ParameterBag $params)
    {
        $reply = parent::actionEdit($params);

        $this->getSignatureOnceControllerPlugin()->setContentsFromCurrentPage(
            $reply,
            'post',
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