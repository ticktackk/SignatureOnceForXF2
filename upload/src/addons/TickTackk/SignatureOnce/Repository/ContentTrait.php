<?php

namespace TickTackk\SignatureOnce\Repository;

use TickTackk\SignatureOnce\Entity\ContainerInterface;
use TickTackk\SignatureOnce\Entity\ContentInterface as EntityContentInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait as EntityContentTrait;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;

/**
 * Trait ContentTrait
 *
 * @package TickTackk\SignatureOnce\Repository
 */
trait ContentTrait
{
    /**
     * @param Entity|ContainerInterface                                     $container
     * @param ArrayCollection|EntityContentTrait[]|EntityContentInterface[] $messages
     * @param int                                                           $page
     * @param null                                                          $messageIds
     *
     * @return ArrayCollection
     */
    public function setShowSignature(ContainerInterface $container, ArrayCollection $messages, $page, $messageIds = null)
    {
        if ($messageIds === null)
        {
            /** @var \XF\App $app */
            $app = $this->app();
            $cache = $app->cache();
            $cacheId = 'tckSignatureOnce_' . $container->getEntityContentType() . '_' . $container->getEntityId();
            if ($this->showSignatureOncePerPage())
            {
                $cacheId .= '_pp';
            }

            if ($cache)
            {
                $messageIds = $cache->fetch($cacheId);
            }

            if (!$messageIds)
            {
                $messageIds = $this->getMessageCountsForSignatureOnce($container, $messages, $page);
                $cache->save($cacheId, $messageIds, 300);
            }
        }

        foreach ($messages AS $messageId => $message)
        {
            $messages[$messageId]->setShowSignature(empty($messageIds[$messageId]));
        }

        return $messages;
    }
}