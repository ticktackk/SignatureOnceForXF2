<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContentTrait;
use TickTackk\SignatureOnce\XF\Entity\ConversationMaster as ConversationMasterEntity;

/**
 * Class ConversationMessage
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 *
 * RELATIONS
 * @property ConversationMasterEntity Conversation
 */
class ConversationMessage extends XFCP_ConversationMessage
{
    use ContentTrait;

    /**
     * @inheritDoc
     */
    protected function getContainerRelationNameForTckSignatureOnce(): string
    {
        return 'Conversation';
    }
}