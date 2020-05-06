<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerInterface;
use XF\Phrase;

/**
 * Class ConversationMaster
 * Extends \XF\Entity\ConversationMaster
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 */
class ConversationMaster extends XFCP_ConversationMaster implements ContainerInterface
{
    /**
     * @inheritDoc
     */
    public function canBypassSignatureOnce(Phrase &$error = null) : bool
    {
        $visitor = \XF::visitor();
        return $visitor->hasPermission('conversation', 'bypassSignatureOnce');
    }
}