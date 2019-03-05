<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerInterface;

/**
 * Class ConversationMaster
 * Extends \XF\Entity\ConversationMaster
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 */
class ConversationMaster extends XFCP_ConversationMaster implements ContainerInterface
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