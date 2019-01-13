<?php

namespace TickTackk\SignatureOnce\XF\Entity;

/**
 * Class Post
 *
 * @package TickTackk\SignatureOnce
 */
class Post extends XFCP_Post
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
    public function canShowSignature(/** @noinspection PhpUnusedParameterInspection */&$error = null)
    {
        if ($this->canBypassSignatureOnce())
        {
            return true;
        }

        if (!$thread = $this->Thread)
        {
            return false;
        }

        return $this->showSignature;
    }

    /**
     * @param null|string $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(/** @noinspection PhpUnusedParameterInspection */ &$error = null)
    {
        $thread = $this->Thread;
        $visitor = \XF::visitor();

        $nodeId = $thread->node_id;

        return $visitor->hasNodePermission($nodeId, 'bypassSignatureOnce');
    }
}