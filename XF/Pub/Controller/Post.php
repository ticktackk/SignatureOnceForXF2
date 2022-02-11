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
 * @version 2.0.3
 */
class Post extends XFCP_Post
{
    /**
     * @since 2.0.3
     *
     * @param ParameterBag $params
     *
     * @return AbstractReply|ViewReply
     *
     * @throws \Exception
     */
    public function actionShow(ParameterBag $params)
    {
        $reply = parent::actionShow($params);

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
     * @version 2.0.0
     *
     * @param ParameterBag $params
     *
     * @return AbstractReply|ErrorReply|RedirectReply|ViewReply
     *
     * @throws \Exception
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