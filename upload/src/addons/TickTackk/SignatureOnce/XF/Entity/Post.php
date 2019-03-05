<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContentInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait;

/**
 * Class Post
 *
 * @package TickTackk\SignatureOnce
 *
 * RELATIONS
 * @property \TickTackk\SignatureOnce\XF\Entity\Thread Thread
 */
class Post extends XFCP_Post implements ContentInterface
{
    use ContentTrait;

    /**
     * @param null $error
     *
     * @return bool|null
     */
    public function canShowSignature(&$error = null)
    {
        if (!$this->Thread)
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
     * @param null|string $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(&$error = null)
    {
        if (!$thread = $this->Thread)
        {
            return false;
        }

        return $thread->canBypassSignatureOnce($error);
    }
}