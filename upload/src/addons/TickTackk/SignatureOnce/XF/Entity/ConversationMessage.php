<?php

namespace TickTackk\SignatureOnce\XF\Entity;

/**
 * Class ConversationMessage
 * 
 * Extends \XF\Entity\ConversationMessage
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 *
 * RELATIONS
 * @property \TickTackk\SignatureOnce\XF\Entity\ConversationMaster Conversation
 */
class ConversationMessage extends XFCP_ConversationMessage
{
    /**
     * @var null|bool
     */
    protected $showSignature;

    /**
     * @param null|bool $showSignature
     */
    public function setShowSignature($showSignature = null)
    {
        $this->showSignature = $showSignature;
    }

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