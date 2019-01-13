<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class ConversationMaster
 * 
 * Extends \XF\Entity\ConversationMaster
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 */
class ConversationMaster extends XFCP_ConversationMaster
{
    /**
     * @param null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(/** @noinspection PhpUnusedParameterInspection */&$error = null)
    {
        return \XF::visitor()->hasPermission('conversation', 'bypassSignatureOnce');
    }
}