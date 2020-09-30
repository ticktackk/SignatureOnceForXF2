<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use TickTackk\SignatureOnce\ControllerPlugin\SignatureOnce as SignatureOnceControllerPlugin;
use XF\ControllerPlugin\AbstractPlugin as AbstractControllerPlugin;
use XF\Entity\Post as PostEntity;
use XF\Mvc\Entity\AbstractCollection;
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

        $forceCacheSuffix = null;
        if ($reply instanceof ViewReply && \XF::$versionId >= 2020010)
        {
            $forceCacheSuffix = $reply->getParam('effectiveOrder');
        }

        $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
        /** @noinspection PhpUndefinedFieldInspection */
        $signatureOnceControllerPlugin->setShowSignature(
            $reply,
            'thread',
            'posts',
            $this->filterPage($parameterBag->page),
            $forceCacheSuffix
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
        /** @noinspection PhpUndefinedMethodInspection */
        $reply = parent::getNewPostsReply($thread, $lastDate);

        if (\XF::$versionId < 2020010)
        {
            $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
            $signatureOnceControllerPlugin->setShowSignature($reply, 'thread', 'posts', null);
        }

        return $reply;
    }

    /**
     * @since XenForo 2.2
     */
    protected function getNewPostsReplyInternal(ThreadEntity $thread, AbstractCollection $posts, PostEntity $firstUnshownPost = null)
    {
        $reply = parent::getNewPostsReplyInternal($thread, $posts, $firstUnshownPost);

        if (\XF::$versionId >= 2020010)
        {
            $signatureOnceControllerPlugin = $this->getSignatureOnceControllerPlugin();
            $signatureOnceControllerPlugin->setShowSignature($reply, 'thread', 'posts', null);
        }

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