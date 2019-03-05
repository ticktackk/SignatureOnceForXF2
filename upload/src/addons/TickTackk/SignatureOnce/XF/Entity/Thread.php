<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerInterface;

/**
 * Class Thread
 * Extends \XF\Entity\Thread
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 */
class Thread extends XFCP_Thread implements ContainerInterface
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