<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use TickTackk\SignatureOnce\ControllerPlugin\SignatureOnce as SignatureOnceControllerPlugin;
use XF\ControllerPlugin\AbstractPlugin as AbstractControllerPlugin;
use XF\Entity\Post as PostEntity;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Entity\Thread as ThreadEntity;

/**
 * @since 2.0.0 Alpha 1
 */
class Thread extends XFCP_Thread
{
    /**
     * @version  2.0.0 Alpha 1
     *
     * @param ParameterBag $params
     *
     * @return AbstractReply|ErrorReply|RedirectReply|ViewReply
     */
    public function actionIndex(ParameterBag $params)
    {
        $reply = parent::actionIndex($params);

        $this->getSignatureOnceControllerPlugin()->setContentsFromCurrentPage(
            $reply,
            'thread',
            'posts',
            $this->filterPage($params->get('page'))
        );

        return $reply;
    }

    /**
     * @version  2.0.0 Alpha 1
     *
     * @param ThreadEntity $thread
     * @param int $lastDate
     *
     * @return ViewReply
     *
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function getNewPostsReply(ThreadEntity $thread, $lastDate)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $reply = parent::getNewPostsReply($thread, $lastDate);

        if (\XF::$versionId < 2020010)
        {
            $this->getSignatureOnceControllerPlugin()->setContentsFromCurrentPage(
                $reply,
                'thread',
                'posts',
                null
            );
        }

        return $reply;
    }

    /**
     * @since XenForo 2.2
     * @version  2.0.0 Alpha 1
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function getNewPostsReplyInternal(ThreadEntity $thread, AbstractCollection $posts, PostEntity $firstUnshownPost = null)
    {
        $reply = parent::getNewPostsReplyInternal($thread, $posts, $firstUnshownPost);

        if (\XF::$versionId >= 2020010)
        {
            $this->getSignatureOnceControllerPlugin()->setContentsFromCurrentPage(
                $reply,
                'thread',
                'posts',
                null
            );
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