<?php

namespace TickTackk\SignatureOnce\Entity;

/**
 * Interface ContentInterface
 *
 * @package TickTackk\SignatureOnce\Entity
 */
interface ContentInterface
{
    /**
     * @param null $error
     *
     * @return bool
     */
    public function canShowSignature(&$error = null);

    /**
     * @param null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(&$error = null);
}