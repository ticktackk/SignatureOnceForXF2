<?php

namespace TickTackk\SignatureOnce\Entity;

/**
 * Interface ContainerInterface
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 */
interface ContainerInterface
{
    /**
     * @param null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(&$error = null);
}