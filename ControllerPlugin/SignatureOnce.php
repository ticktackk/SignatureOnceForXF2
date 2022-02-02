<?php

namespace TickTackk\SignatureOnce\ControllerPlugin;

use TickTackk\SignatureOnce\Entity\SignatureOnceTrait;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Mvc\Reply\View;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\AbstractReply;
use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;

/**
 * @version 2.0.0 Alpha 1
 */
class SignatureOnce extends AbstractPlugin
{
    /**
     * @param AbstractReply $reply
     * @param string $contentType
     * @param string $containerKey
     * @param string $messagesKey
     * @param string|null $pageKey
     *
     * @return void
     *
     * @throws \Exception
     */
    public function setContentsFromCurrentPage(
        AbstractReply $reply,
        string $contentType,
        string $containerKey,
        string $messagesKey,
        ?string $pageKey = 'page'
    ) : void
    {
        if (!$reply instanceof ViewReply)
        {
            return;
        }

        $handler = $this->getSignatureOnceRepo()->getHandler($contentType);

        /** @var SignatureOnceTrait|Entity $container */
        $container = $reply->getParam($containerKey);
        if (!$container instanceof Entity || !$handler->isSignatureShownOncePerPage($container))
        {
            return;
        }

        $messages = $reply->getParam($messagesKey);
        if ($messages instanceof ArrayCollection)
        {
            $messages = $messages->toArray();
        }
        else if ($messages instanceof Entity)
        {
            $messages = [$messages->getEntityId() => $messages];
        }
        else if (!is_array($messages))
        {
            return;
        }
        $handler->setContents($messages);

        if (is_int($pageKey))
        {
            $page = $pageKey;
        }
        else if ($pageKey === null)
        {
            $page = $handler->getCalculatedPageFromContents($container);
        }
        else
        {
            if (!array_key_exists($pageKey, $reply->getParams()))
            {
                return;
            }

            $page = max(1, $reply->getParam($pageKey));
        }
        $handler->setPage($page);

        // from quick reply so force loading all messages
        if ($pageKey === null && !$handler->hasLoadedAllContents($container))
        {
            $handler->loadAllContents($container);
        }
        else
        {
            $handler->setContents($messages);
        }
    }

    /**
     * @return Repository|SignatureOnceRepo
     */
    protected function getSignatureOnceRepo() : SignatureOnceRepo
    {
        return $this->repository('TickTackk\SignatureOnce:SignatureOnce');
    }
}