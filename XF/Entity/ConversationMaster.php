<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerInterface as ContainerEntityInterface;
use TickTackk\SignatureOnce\Entity\ContainerTrait as ContainerEntityTrait;
use TickTackk\SignatureOnce\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\SignatureOnce\XF\Entity\ConversationMessage as ExtendedConversationMessageEntity;
use XF\Db\AbstractAdapter as DbAdapter;
use XF\Phrase;
use XF\Repository\ConversationMessage as ConversationMessageRepo;

/**
 * @version 2.0.0 Alpha 1
 */
class ConversationMaster extends XFCP_ConversationMaster implements ContainerEntityInterface
{
    use ContainerEntityTrait;

    /**
     * @inheritDoc
     */
    public function canBypassSignatureOnce(Phrase &$error = null) : bool
    {
        $visitor = \XF::visitor();
        return $visitor->hasPermission('conversation', 'bypassSignatureOnce');
    }

    /**
     * @inheritDoc
     */
    public function isSignatureShownOncePerContainerForTckSignatureOnce(): bool
    {
        return $this->app()->options()->showSignatureOncePerConversation;
    }

    /**
     * @inheritDoc
     */
    public function loadAllContentsFromCurrentPageForTckSignatureOnce(int $page) : void
    {
        /** @var ConversationMessageRepo $convMessageRepo */
        $convMessageRepo = $this->repository('XF:ConversationMessage');
        $contents = $convMessageRepo->findMessagesForConversationView($this)
            ->limitByPage($page, $this->getContentsPerPageForTckSignatureOnce())
            ->fetch()
            ->toArray();

        $this->setContentsFromCurrentPageForTckSignatureOnce($contents);
    }

    /**
     * @inheritDoc
     */
    public function getExpectedFirstContentForTckSignatureOnce(
        int $userId,
        string $contentType
    ): ?ContentEntityInterface
    {
        /** @var ConversationMessageRepo $convMessageRepo */
        $convMessageRepo = $this->repository('XF:ConversationMessage');

        /** @var ExtendedConversationMessageEntity $convMessage */
        $convMessage = $convMessageRepo->findMessagesForConversationView($this)
            ->where('user_id', $userId)
            ->order('message_date')
            ->fetchOne();

        return $convMessage;
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $this->containerPostDeleteForTckSignatureOnce();
    }

    /**
     * @inheritDoc
     */
    public function rebuildContainerFirstUserContentRecordsQueryForTckSignatureOnce(
        DbAdapter $db
    ): void
    {
        $db->query("
			INSERT INTO xf_tck_signature_once_container_first_user_content
			    (user_id, container_type, container_id, content_type, content_id, content_date)
			    SELECT user_id, ?, conversation_id, ?, message_id, message_date
			    FROM xf_conversation_message
			    WHERE conversation_id = ?
				  AND user_id > 0
			    GROUP BY user_id ASC
		", [$this->getEntityContentType(), 'post', $this->getEntityId()]);
    }

    /**
     * @inheritDoc
     */
    public function getContentsPerPageForTckSignatureOnce(): int
    {
        return $this->app()->options()->messagesPerPage;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentPageFromMessagesForTckSignatureOnce(
        array $contents
    ): int
    {
        $lastDate = \max(\array_column($contents, 'message_date'));

        /** @var ConversationMessageRepo $conversationMessageRepo */
        $conversationMessageRepo = $this->repository('XF:ConversationMessage');
        $conversationMessageTotal = $conversationMessageRepo->findMessagesForConversationView($this)
            ->where('message_date', '<', $lastDate)
            ->total();

        return \floor($conversationMessageTotal / $this->getContentsPerPageForTckSignatureOnce()) + 1;
    }
}