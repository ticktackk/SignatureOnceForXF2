<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerFirstUserContent as ContainerFirstUserContentEntity;
use TickTackk\SignatureOnce\Entity\ContainerInterface as ContainerEntityInterface;
use TickTackk\SignatureOnce\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait as ContentEntityTrait;
use TickTackk\SignatureOnce\XF\Entity\ConversationMaster as ConversationMasterEntity;
use XF\Mvc\Entity\Structure as EntityStructure;

/**
 * @since 2.0.0 Alpha 1
 *
 * RELATIONS
 * @property ConversationMasterEntity Conversation
 * @property ContainerFirstUserContentEntity ConversationFirstUserMessage
 */
class ConversationMessage extends XFCP_ConversationMessage implements ContentEntityInterface
{
    use ContentEntityTrait;

    /**
     * @inheritDoc
     */
    public function getContainerForTckSignatureOnce(): ?ContainerEntityInterface
    {
        return $this->Conversation;
    }

    /**
     * @inheritDoc
     */
    public function getContainerFirstUserContentForTckSignatureOnce(): ?ContainerFirstUserContentEntity
    {
        return $this->ConversationFirstUserMessage;
    }

    /**
     * @inheritDoc
     */
    public function getContainerTypeForTckSignatureOnce(): string
    {
        return 'conversation';
    }

    /**
     * @inheritDoc
     */
    public function getContainerIdFroTckSignatureOnce(): int
    {
        return $this->conversation_id;
    }

    /**
     * @inheritDoc
     */
    public function getContentDateForTckSignatureOnce(): int
    {
        return $this->message_date;
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     */
    protected function _postSave()
    {
        parent::_postSave();

        $this->adjustContainerFirstUserContentRecordForTckSignatureOnce();
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        $this->adjustContainerFirstUserContentRecordForTckSignatureOnce();
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param EntityStructure $structure
     *
     * @return EntityStructure
     *
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function getStructure(EntityStructure $structure)
    {
        $structure = parent::getStructure($structure);

        static::setupContainerEntityStructureForTckSignatureOnce(
            $structure,
            'ConversationFirstUserMessage',
            'conversation',
            'conversation_id'
        );

        return $structure;
    }
}