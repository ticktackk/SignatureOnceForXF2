<?php

namespace TickTackk\SignatureOnce\XF\Entity;

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
    public function canBypassSignatureOnce(/** @noinspection PhpUnusedParameterInspection */&$error = null)
    {
        return \XF::visitor()->hasNodePermission($this->node_id, 'bypassSignatureOnce');
    }
}