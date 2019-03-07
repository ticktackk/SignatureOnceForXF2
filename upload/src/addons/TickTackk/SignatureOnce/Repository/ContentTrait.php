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
            $messageIds = $this->getMessageCountsForSignatureOnce($container, $messages, $page);
        }

        foreach ($messages AS $messageId => $message)
        {
            $messages[$messageId]->setShowSignature(empty($messageIds[$messageId]));
        }

        return $messages;
    }
}