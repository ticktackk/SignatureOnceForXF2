<?php

namespace TickTackk\SignatureOnce\XF\Repository;

use XF\Mvc\Entity\ArrayCollection;

/**
 * Class ConversationMessage
 * 
 * Extends \XF\Repository\ConversationMessage
 *
 * @package TickTackk\SignatureOnce\XF\Repository
 */
class ConversationMessage extends XFCP_ConversationMessage
{
    /**
     * @param \XF\Entity\ConversationMaster                                            $master
     * @param ArrayCollection|\TickTackk\SignatureOnce\XF\Entity\ConversationMessage[] $messages
     * @param null|array                                                               $messageCounts
     *
     * @return ArrayCollection
     */
    public function setConversationsShowSignature(\XF\Entity\ConversationMaster $master, ArrayCollection $messages, $messageCounts = null)
    {
        if ($messageCounts === null)
        {
            $messageCounts = $this->getMessageCountsForSignatureOnce($master, $messages);
        }

        foreach ($messages AS $conversationMessageId => $conversationMessage)
        {
            $showOncePerConversation = $this->options()->showSignatureOncePerConversation;
            if ($showOncePerConversation)
            {
                $showSignature = !isset($messageCounts[$conversationMessageId]);
            }
            else
            {
                $showSignature = isset($messageCounts[$conversationMessageId]);
            }
            $messages[$conversationMessageId]->setShowSignature($showSignature);
        }

        return $messages;
    }

    /**
     * @param \XF\Entity\ConversationMaster $master
     * @param ArrayCollection|\TickTackk\SignatureOnce\XF\Entity\ConversationMessage[] $messages
     *
     * @return array
     */
    public function getMessageCountsForSignatureOnce(\XF\Entity\ConversationMaster $master, ArrayCollection $messages)
    {
        $db = $this->db();

        /** @var \TickTackk\SignatureOnce\XF\Entity\ConversationMessage $firstMessage */
        $firstMessage = $messages->first();

        /** @var \TickTackk\SignatureOnce\XF\Entity\ConversationMessage $lastMessage */
        $lastMessage = $messages->last();

        $oncePerContent = 'conversation_message_tmp.message_id < conversation_message.message_id AND';
        $groupBy = 'message_id';

        if (!$this->options()->showSignatureOncePerConversation)
        {
            $oncePerContent = '';
            $groupBy = 'user_id';
        }

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
        ", [$master->conversation_id, $master->conversation_id, $firstMessage->message_id, $lastMessage->message_id]);
    }
}