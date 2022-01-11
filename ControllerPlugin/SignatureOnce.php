<?php

namespace TickTackk\SignatureOnce\ControllerPlugin;

use TickTackk\SignatureOnce\Entity\ContainerInterface as ContainerEntityInterface;
use TickTackk\SignatureOnce\Entity\ContainerTrait as ContainerEntityTrait;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Reply\View;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\AbstractReply;

/**
 * Class SignatureOnce
 *
 * @package TickTackk\SignatureOnce\ControllerPlugin
 */
class SignatureOnce extends AbstractPlugin
{
    public function setContentsFromCurrentPage(
        AbstractReply $reply,
        $containerKey,
        $messagesKey,
        $pageKey = 'page'
    ) : void
    {
        if (!$reply instanceof ViewReply)
        {
            return;
        }

        /** @var ContainerEntityInterface|ContainerEntityTrait|Entity $container */
        $container = $reply->getParam($containerKey);
        if (!$container instanceof ContainerEntityInterface || $container->isSignatureShownOncePerContainerForTckSignatureOnce())
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
        else if (!\is_array($messages))
        {
            return;
        }

        if (\is_int($pageKey))
        {
            $page = $pageKey;
        }
        else if ($pageKey === null)
        {
            $page = $container->getCurrentPageFromMessagesForTckSignatureOnce($messages);
        }
        else
        {
            if (!\array_key_exists($pageKey, $reply->getParams()))
            {
                return;
            }

            $page = \max(1, $reply->getParam($pageKey));
        }
        $container->setViewingPageForTckSignatureOnce($page);

        // from quick reply so force loading all messages
        if ($pageKey === null && \count($messages) !== $container->getContentsPerPageForTckSignatureOnce())
        {
            $container->loadAllContentsFromCurrentPageForTckSignatureOnce($page);
        }
        else
        {
            $container->setContentsFromCurrentPageForTckSignatureOnce($messages);
        }
    }
}