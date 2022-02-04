<?php

namespace TickTackk\SignatureOnce\Repository;

use TickTackk\SignatureOnce\SignatureOnce\AbstractHandler as SignatureOnceHandler;
use XF\Mvc\Entity\Repository;

/**
 * @since 2.0.0
 */
class SignatureOnce extends Repository
{
    protected array $handlerCache = [];

    protected function isHandlerCached(
        string $contentType
    ) : bool
    {
        return array_key_exists($contentType, $this->handlerCache);
    }

    protected function cacheHandler(
        SignatureOnceHandler $handler
    ) : void
    {
        if ($this->isHandlerCached($handler->getContentType()))
        {
            return;
        }

        $this->handlerCache[$handler->getContentType()] = $handler;
    }

    protected function getCachedHandler(
        string $contentType
    ) : SignatureOnceHandler
    {
        return $this->handlerCache[$contentType];
    }

    public function clearCachedHandler(string $contentType) : void
    {
        if (!$this->isHandlerCached($contentType))
        {
            return;
        }

        unset($this->handlerCache[$contentType]);
    }

    /**
     * @param string $type
     * @param bool $fromCache
     * @param bool $throw
     *
     * @return SignatureOnceHandler|null
     *
     * @throws \Exception
     */
    public function getHandler(
        string $type,
        bool $fromCache = true,
        bool $throw = false
    ) :? SignatureOnceHandler
    {
        if ($fromCache && $this->isHandlerCached($type))
        {
            return $this->getCachedHandler($type);
        }

        $handlerClass = $this->app()->getContentTypeFieldValue($type, 'tck_signature_once_handler_class');
        if (!$handlerClass)
        {
            if ($throw)
            {
                throw new \InvalidArgumentException("No Signature Once handler for '$type'");
            }

            return null;
        }

        if (!class_exists($handlerClass))
        {
            if ($throw)
            {
                throw new \InvalidArgumentException("Signature Once handler for '$type' does not exist: $handlerClass");
            }

            return null;
        }

        $handlerClass = $this->app()->extendClass($handlerClass);

        /** @var SignatureOnceHandler $handler */
        $handler = new $handlerClass($type);
        if ($fromCache)
        {
            $this->cacheHandler($handler);
        }

        return $handler;
    }
}