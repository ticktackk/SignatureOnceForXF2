<?php

namespace TickTackk\SignatureOnce\XF\Repository;

use TickTackk\SignatureOnce\Entity\ContainerInterface;
use TickTackk\SignatureOnce\Repository\ContentInterface;
use TickTackk\SignatureOnce\Repository\ContentTrait;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
use TickTackk\SignatureOnce\Entity\ContentTrait as EntityContentTrait;
use TickTackk\SignatureOnce\Entity\ContentInterface as EntityContentInterface;

/**
 * Class ConversationMessage
 * 
 * Extends \XF\Repository\ConversationMessage
 *
 * @package TickTackk\SignatureOnce\XF\Repository
 */
class ConversationMessage extends XFCP_ConversationMessage implements ContentInterface
{
    use ContentTrait;

    /**
     * @return bool
     */
    public function showSignatureOncePerPage()
    {
        return !$this->options()->showSignatureOncePerConversation;
    }

    /**
     * @param Entity|ContainerInterface $container
     * @param ArrayCollection|EntityContentTrait[]|EntityContentInterface[]    $messages
     * @param                    $page
     *
     * @return array
     */
    public function getMessageCountsForSignatureOnce(/** @noinspection PhpUnusedParameterInspection */Entity $container, ArrayCollection $messages, $page)
    {
        $db = $this->db();

        /** @var \TickTackk\SignatureOnce\XF\Entity\ConversationMessage $firstMessage */
        $firstMessage = $messages->first();
        /** @var \TickTackk\SignatureOnce\XF\Entity\ConversationMessage $lastMessage */
        $lastMessage = $messages->last();

        $oncePerContent = 'conversation_message_tmp.message_id < conversation_message.message_id AND';
        $groupBy = 'message_id';

        if ($this->showSignatureOncePerPage())
        {
            $oncePerContent = '';
            $groupBy = 'user_id';
        }

        /** @var \TickTackk\SignatureOnce\XF\Entity\ConversationMaster $container */
        return $db->fetchPairs("
            SELECT conversation_message.message_id, COUNT(*)
            FROM xf_conversation_message AS conversation_message
            INNER JOIN xf_conversation_message AS conversation_message_tmp ON 
            (
                  {$oncePerContent}
                  conversation_message_tmp.user_id = conversation_message.user_id AND 
                  conversation_message_tmp.conversation_id = conversation_message.conversation_id
            )
            WHERE conversation_message.conversation_id = ?
              AND conversation_message_tmp.conversation_id = ?
              AND conversation_message.message_id >= ?
              AND conversation_message.message_id < ?
            GROUP BY conversation_message.{$groupBy}
            ORDER BY message_id ASC
        ", [$container->conversation_id, $container->conversation_id, $firstMessage->message_id, $lastMessage->message_id]);
    }
}