<?php

namespace TickTackk\SignatureOnce\XF\Entity;

/**
 * Class Post
 *
 * @package TickTackk\SignatureOnce
 *
 * RELATIONS
 * @property \TickTackk\SignatureOnce\XF\Entity\Thread Thread
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
        if (!$this->Thread)
        {
            return false;
        }

        if ($this->canBypassSignatureOnce())
        {
            return true;
        }

        return $this->showSignature;
    }

    /**
     * @param null|string $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(/** @noinspection PhpUnusedParameterInspection */&$error = null)
    {
        if (!$thread = $this->Thread)
        {
            return false;
        }

        return $thread->canBypassSignatureOnce($error);
    }
}