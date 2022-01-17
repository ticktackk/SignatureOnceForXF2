<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\SignatureOnceTrait;

/**
 * @version 2.0.0 Alpha 1
 */
class Thread extends XFCP_Thread
{
    use SignatureOnceTrait;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        $this->getHandlerForTckSignatureOnce('post')->containerPostDelete($this);
    }
}