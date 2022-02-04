<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerFirstUserContent as ContainerFirstUserContentEntity;
use TickTackk\SignatureOnce\Entity\ContentTrait as ContentEntityTrait;
use TickTackk\SignatureOnce\Entity\SignatureOnceTrait;
use TickTackk\SignatureOnce\XF\Entity\ConversationMaster as ConversationMasterEntity;
use XF\Mvc\Entity\Structure as EntityStructure;

/**
 * @since 2.0.0
 *
 * RELATIONS
 * @property ConversationMasterEntity Conversation
 * @property ContainerFirstUserContentEntity ConversationFirstUserMessage
 */
class ConversationMessage extends XFCP_ConversationMessage
{
    use ContentEntityTrait, SignatureOnceTrait;

    /**
     * @since 2.0.0
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function _postSave()
    {
        parent::_postSave();

        $this->getHandlerForTckSignatureOnce('conversation_message')->adjustContainerFirstUserContentRecord($this);
    }

    /**
     * @since 2.0.0
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        $this->getHandlerForTckSignatureOnce('conversation_message')->adjustContainerFirstUserContentRecord($this);
    }

    /**
     * @since 2.0.0
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