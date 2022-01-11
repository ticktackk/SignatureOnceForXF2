<?php

namespace TickTackk\SignatureOnce\Entity;

use TickTackk\SignatureOnce\Entity\ContainerFirstUserContent as ContainerFirstUserContentEntity;
use TickTackk\SignatureOnce\Entity\ContainerInterface as ContainerEntityInterface;
use XF\Mvc\Entity\Entity;

/**
 * @since 2.0.0 Alpha 1
 */
interface ContentInterface
{
    /**
     * @since 2.0.0 Alpha 1
     *
     * @return null|Entity|ContainerInterface
     */
    public function getContainerForTckSignatureOnce() :? ContainerEntityInterface;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return ContainerFirstUserContent|null
     */
    public function getContainerFirstUserContentForTckSignatureOnce() :? ContainerFirstUserContentEntity;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return string
     */
    public function getContainerTypeForTckSignatureOnce() : string;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return int
     */
    public function getContainerIdFroTckSignatureOnce() : int;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return int
     */
    public function getContentDateForTckSignatureOnce() : int;
}