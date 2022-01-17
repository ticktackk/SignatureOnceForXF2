<?php

namespace TickTackk\SignatureOnce\Entity;

use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;
use TickTackk\SignatureOnce\SignatureOnce\AbstractHandler as SignatureOnceHandler;
use XF\Mvc\Entity\Repository;

/**
 * @since 2.0.0 Alpha 1
 */
trait SignatureOnceTrait
{
    /**
     * @return Repository|SignatureOnceRepo
     */
    protected function getSignatureOnceRepo() : SignatureOnceRepo
    {
        return $this->repository('TickTackk\SignatureOnce:SignatureOnce');
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param string $contentType
     * @param bool $fromCache
     * @param bool $throw
     *
     * @return SignatureOnceHandler
     *
     * @throws \Exception
     */
    protected function getHandlerForTckSignatureOnce(
        string $contentType,
        bool $fromCache = true,
        bool $throw = false
    ) : SignatureOnceHandler
    {
        return $this->getSignatureOnceRepo()->getHandler($contentType, $fromCache, $throw);
    }
}