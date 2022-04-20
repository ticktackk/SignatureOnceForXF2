<?php

namespace TickTackk\SignatureOnce\SV\LiveContent\ControllerPlugin;

use SV\LiveContent\ILiveContent;
use TickTackk\SignatureOnce\ControllerPlugin\SignatureOnce as SignatureOnceControllerPlugin;
use XF\App as BaseApp;
use XF\ControllerPlugin\AbstractPlugin as AbstractControllerPlugin;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Service\AbstractService;
use XF\Mvc\Entity\Manager as EntityManager;
use XF\Job\Manager as JobManager;

/**
 * @since 2.0.4
 */
class Poller extends XFCP_Poller
{
    /**
     * @param string $contentType
     * @param ILiveContent $entity
     * @param callable $getContents
     * @param int|null $limit
     *
     * @return AbstractReply|ViewReply
     *
     * @throws \Exception
     */
    public function poll(
        $contentType,
        ILiveContent $entity,
        callable $getContents,
        $limit = null
    )
    {
        $reply = parent::poll($contentType, $entity, $getContents, $limit);

        if ($reply instanceof ViewReply)
        {
            $reply->setParam('tckSignatureOnceContainer', $entity);
            $this->getSignatureOnceControllerPlugin()->setContentsFromCurrentPage(
                $reply,
                $contentType,
                'tckSignatureOnceContainer',
                'contents',
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