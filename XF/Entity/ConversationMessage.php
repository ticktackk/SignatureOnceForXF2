<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContentInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait;

/**
 * Class ConversationMessage
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 *
 * RELATIONS
 * @property \TickTackk\SignatureOnce\XF\Entity\ConversationMaster Conversation
 */
class ConversationMessage extends XFCP_ConversationMessage implements ContentInterface
{
    use ContentTrait;

    /**
     * @param null $error
     *
     * @return bool|null
     */
    public function canShowSignature(&$error = null)
    {
        if (!$this->Conversation)
        {
            return false;
        }

        if ($this->canBypassSignatureOnce($error))
        {
            return true;
        }

        return $this->showSignature;
    }

    /**
     * @param null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(&$error = null)
    {
        if (!$conversation = $this->Conversation)
        {
            return false;
        }

        return $conversation->canBypassSignatureOnce($error);
    }
}