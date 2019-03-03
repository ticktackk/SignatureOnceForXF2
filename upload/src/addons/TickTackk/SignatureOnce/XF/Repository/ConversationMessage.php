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
     * @var string
     */
    protected $columnPrefixForPostData = 'convMsg_';

    /**
     * @param ArrayCollection|\TickTackk\SignatureOnce\XF\Entity\ConversationMessage[] $conversationMessages
     * @param int             $page
     * @param null            $messageCounts
     *
     * @return ArrayCollection
     */
    public function setConversationsShowSignature(ArrayCollection $conversationMessages, $page, $messageCounts = null)
    {
        if ($messageCounts === null)
        {
            $messageCounts = $this->getMessageCountsForSignatureOnce($conversationMessages, $page);
        }

        foreach ($messageCounts AS $messageId => $messageCount)
        {
            $messageIdInt = (int) utf8_substr($messageId, utf8_strlen($this->columnPrefixForPostData));
            if (isset($conversationMessages[$messageIdInt]))
            {
                $conversationMessages[$messageIdInt]->setShowSignature($messageCount === 0);
            }
        }

        return $conversationMessages;
    }

    /**
     * @param ArrayCollection|\TickTackk\SignatureOnce\XF\Entity\ConversationMessage[] $conversationMessages
     * @param int             $page
     *
     * @return array|bool|false
     */
    public function getMessageCountsForSignatureOnce(ArrayCollection $conversationMessages, $page)
    {
        $db = $this->app()->db();
        $perConversation = $this->options()->showSignatureOncePerConversation;
        $perPage = $this->options()->messagesPerPage;

        $queries = [];

        /** @var \TickTackk\SignatureOnce\XF\Entity\ConversationMessage $firstConversationMessage */
        $firstConversationMessage = $conversationMessages->first();
        foreach ($conversationMessages AS $conversationMessage)
        {
            if ($conversation = $conversationMessage->Conversation)
            {
                /** @var \XF\Finder\ConversationMessage $conversationMessageFinder */
                $conversationMessageFinder = $this->finder('XF:ConversationMessage');
                $conversationMessageFinder->inConversation($conversation)
                    ->where('user_id', $conversationMessage->user_id)
                    ->where('message_id', '<', $conversationMessage->message_id);

                if (!$perConversation)
                {
                    $conversationMessageFinder
                        ->where('message_id', '>=', $firstConversationMessage->message_id)
                        ->limitByPage($page, $perPage);
                }

                $queries[] = "({$conversationMessageFinder->getQuery(['countOnly' => true])}) AS {$this->columnPrefixForPostData}{$conversationMessage->message_id}";
            }
        }

        return $db->fetchRow('SELECT ' . implode(', ', $queries) . ' FROM xf_conversation_message LIMIT 1');
    }
}