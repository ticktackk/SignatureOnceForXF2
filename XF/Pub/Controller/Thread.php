<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use TickTackk\SignatureOnce\ControllerPlugin\SignatureOnce as SignatureOnceControllerPlugin;
use XF\ControllerPlugin\AbstractPlugin as AbstractControllerPlugin;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Entity\Thread as ThreadEntity;

/**
 * Class Thread
 *
 * Extends \XF\Pub\Controller\Thread
 *
 * @package TickTackk\SignatureOnce\XF\Pub\Controller
 */
class Thread extends XFCP_Thread
{
    /**
     * @param ParameterBag $parameterBag
     *
     * @return ErrorReply|RedirectReply|ViewReply
     */
    public function actionIndex(ParameterBag $parameterBag)
    {
        $reply = parent::actionIndex($parameterBag);

        $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
        /** @noinspection PhpUndefinedFieldInspection */
        $signatureOnceControllerPlugin->setShowSignature(
            $reply,
            'thread',
            'posts',
            $this->filterPage($parameterBag->page)
        );

        return $reply;
    }

    /**
     * @param ThreadEntity $thread
     * @param int $lastDate
     *
     * @return ViewReply
     */
    protected function getNewPostsReply(ThreadEntity $thread, $lastDate)
    {
        $reply = parent::getNewPostsReply($thread, $lastDate);

        $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
        $signatureOnceControllerPlugin->setShowSignature($reply, 'thread', 'posts', null);

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