<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerInterface;
use XF\Phrase;

/**
 * Class Thread
 * Extends \XF\Entity\Thread
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 */
class Thread extends XFCP_Thread implements ContainerInterface
{
    /**
     * @inheritDoc
     */
    public function canBypassSignatureOnce(Phrase &$error = null) : bool
    {
        $visitor = \XF::visitor();
        return $visitor->hasNodePermission($this->node_id, 'bypassSignatureOnce');
    }

    /**
     * @inheritDoc
     */
    public function getLastModifiedTimestampForSignatureOnce(): int
    {
        return $this->last_post_date;
    }
}