<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class Thread
 * 
 * Extends \XF\Entity\Thread
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 */
class Thread extends XFCP_Thread
{
    /**
     * @param null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(&$error = null)
    {
        return \XF::visitor()->hasNodePermission($this->node_id, 'bypassSignatureOnce');
    }
}