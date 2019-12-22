<?php

namespace TickTackk\SignatureOnce\XF\Repository;

use TickTackk\SignatureOnce\Entity\ContainerInterface;
use TickTackk\SignatureOnce\Entity\ContentInterface as EntityContentInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait as EntityContentTrait;
use TickTackk\SignatureOnce\Repository\ContentInterface;
use TickTackk\SignatureOnce\Repository\ContentTrait;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;

/**
 * Class ConversationMessage
 * Extends \XF\Repository\ConversationMessage
 *
 * @package TickTackk\SignatureOnce\XF\Repository
 */
class ConversationMessage extends XFCP_ConversationMessage implements ContentInterface
{
    use ContentTrait;

    /**
     * @param Entity|ContainerInterface                                     $container
     * @param ArrayCollection|EntityContentTrait[]|EntityContentInterface[] $messages
     * @param                                                               $page
     *
     * @return array
     */
    public function getMessageCountsForSignatureOnce(/** @noinspection PhpUnusedParameterInspection */
        ContainerInterface $container, ArrayCollection $messages, $page)
    {
        $db = $this->db();

        /**
         * @var \TickTackk\SignatureOnce\XF\Entity\ConversationMaster  $container
         * @var \TickTackk\SignatureOnce\XF\Entity\ConversationMessage $firstMessage
         * @var \TickTackk\SignatureOnce\XF\Entity\ConversationMessage $lastMessage
         */
        $firstMessage = $messages->first();
        $lastMessage = $messages->last();

        $containerId = $db->quote($container->conversation_id);
        $startQuoted = $db->quote($firstMessage->message_id);
        $endQuoted = $db->quote($lastMessage->message_id);

        if ($this->showSignatureOncePerPage())
        {
            $pageCondition = "AND conversation_message_tmp.message_id >= {$startQuoted}";
        }
        else
        {
            $pageCondition = 'AND conversation_message_tmp.message_id < conversation_message_main.message_id';
        }

        return $db->fetchPairs("
            SELECT
                conversation_message_main.message_id,
                (
                  SELECT message_id 
                  FROM xf_conversation_message AS conversation_message_tmp
                  WHERE conversation_message_tmp.user_id = conversation_message_main.user_id
                    {$pageCondition}
                    AND conversation_message_tmp.message_id < conversation_message_main.message_id
                    AND conversation_message_tmp.conversation_id = {$containerId}
                  ORDER BY conversation_message_tmp.message_id ASC
                  LIMIT 1
                ) AS previous_message_id
            FROM
            (
                SELECT DISTINCT user_id, message_id
                FROM xf_conversation_message AS conversation_message
                WHERE conversation_message.conversation_id = {$containerId}
                  AND conversation_message.message_id >= {$startQuoted}
                  AND conversation_message.message_id < {$endQuoted}
            ) AS conversation_message_main
        ");
    }

    /**
     * @return bool
     */
    public function showSignatureOncePerPage()
    {
        return !$this->options()->showSignatureOncePerConversation;
    }
}