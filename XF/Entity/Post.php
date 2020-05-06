<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContentTrait;
use TickTackk\SignatureOnce\XF\Entity\Thread as ExtendedThreadEntity;

/**
 * Class Post
 *
 * @package TickTackk\SignatureOnce
 *
 * RELATIONS
 * @property ExtendedThreadEntity Thread
 */
class Post extends XFCP_Post
{
    use ContentTrait;

    /**
     * @inheritDoc
     */
    protected function getContainerRelationNameForTckSignatureOnce(): string
    {
        return 'Thread';
    }
}