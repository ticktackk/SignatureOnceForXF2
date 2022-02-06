<?php

namespace TickTackk\SignatureOnce\SignatureOnce;

use TickTackk\SignatureOnce\Entity\ContainerFirstUserContent as ContainerFirstUserContentEntity;
use TickTackk\SignatureOnce\XF\Entity\ConversationMessage as ExtendedConversationMessageEntity;
use TickTackk\SignatureOnce\XF\Entity\ConversationMaster as ExtendedConversationMasterEntity;
use XF\Db\AbstractAdapter as DbAdapter;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Phrase;
use XF\Repository\ConversationMessage as ConversationMessageRepo;

/**
 * @since 2.0.0
 */
class ConversationMessage extends AbstractHandler
{
    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMessageEntity $content
     */
    public function canBypassSignatureOnce(
        Entity $content,
        ?Phrase &$error = null
    ): bool
    {
        return \XF::visitor()->hasPermission('conversation', 'bypassSignatureOnce');
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMasterEntity $container
     */
    public function isSignatureShownOncePerPage(Entity $container): bool
    {
        return !$this->options()->tckSignatureOnceShowSignatureOncePerConversation;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMessageEntity $content
     */
    protected function getContainerFromContent(Entity $content): ?Entity
    {
        return $content->Conversation;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMessageEntity $content
     */
    protected function getFirstUserContentRecord(Entity $content): ?ContainerFirstUserContentEntity
    {
        return $content->ConversationFirstUserMessage;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMessageEntity $content
     */
    protected function getUserIdFromContent(Entity $content): int
    {
        return $content->user_id;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMessageEntity $content
     */
    protected function getDateFromContent(Entity $content): int
    {
        return $content->message_date;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMasterEntity $container
     */
    protected function getExpectedFirstContent(
        int $userId,
        Entity $container
    ): ?Entity
    {
        return $this->getConversationMessageRepo()->findMessagesForConversationView($container)
            ->where('user_id', $userId)
            ->fetchOne();
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMasterEntity $container
     */
    protected function getContentsPerPage(Entity $container): int
    {
        return (int) $this->options()->messagesPerPage;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMasterEntity $container
     */
    public function getCalculatedPageFromContents(Entity $container): int
    {
        $lastPosition = max(array_column($this->getContents(), 'position')) + 1;

        return (int) max(1, ceil($lastPosition / $this->getContentsPerPage($container)));
    }

    /**
     * @inheritDoc
     */
    protected function internalRebuildContainerFirstUserContentRecords(
        DbAdapter $db,
        string $containerType,
        string $containerId
    ): void
    {
        $db->query("
			INSERT INTO xf_tck_signature_once_container_first_user_content
			    (user_id, container_type, container_id, content_type, content_id, content_date)
			    SELECT user_id, ?, conversation_id, ?, message_id, message_date
			    FROM xf_conversation_message
			    WHERE conversation_id = ?
				  AND user_id > 0
			    GROUP BY user_id
		", [$containerType, $this->getContentType(), $containerId]);
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedConversationMasterEntity $container
     */
    public function loadAllContents(
        Entity $container
    ): void
    {
        $page = $this->getPage();
        $perPage = $this->getContentsPerPage($container);

        $messageFinder = $this->getConversationMessageRepo()
            ->findMessagesForConversationView($container)
            ->limitByPage($page, $perPage);

        $this->setContents($messageFinder->fetch()->toArray());
    }

    /**
     * @return Repository|ConversationMessageRepo
     */
    protected function getConversationMessageRepo() : ConversationMessageRepo
    {
        return $this->repository('XF:ConversationMessage');
    }
}