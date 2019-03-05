<?php

namespace TickTackk\SignatureOnce\Repository;

use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
use TickTackk\SignatureOnce\Entity\ContentTrait as EntityContentTrait;
use TickTackk\SignatureOnce\Entity\ContentInterface as EntityContentInterface;
use TickTackk\SignatureOnce\Entity\ContainerInterface;

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
     * @param null                                                          $messageCounts
     *
     * @return ArrayCollection
     */
    public function setShowSignature(ContainerInterface $container, ArrayCollection $messages, $page, $messageCounts = null)
    {
        if ($messageCounts === null)
        {
            $messageCounts = $this->getMessageCountsForSignatureOnce($container, $messages, $page);
        }

        foreach ($messages AS $conversationMessageId => $conversationMessage)
        {
            if ($this->showSignatureOncePerPage())
            {
                $showSignature = isset($messageCounts[$conversationMessageId]);
            }
            else
            {
                $showSignature = !isset($messageCounts[$conversationMessageId]);
            }
            $messages[$conversationMessageId]->setShowSignature($showSignature);
        }

        return $messages;
    }
}