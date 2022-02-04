<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\SignatureOnceTrait;

/**
 * @version 2.0.0
 */
class ConversationMaster extends XFCP_ConversationMaster
{
    use SignatureOnceTrait;

    /**
     * @return void
     *
     * @throws \Exception
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        $this->getHandlerForTckSignatureOnce('conversation_message');
    }
}