<?php

namespace TickTackk\SignatureOnce\Entity;

use XF\Phrase;

/**
 * Interface ContainerInterface
 *
 * @package TickTackk\SignatureOnce\XF\Entity
 */
interface ContainerInterface
{
    /**
     * @param Phrase|null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(Phrase &$error = null) : bool;

    /**
     * @return int
     */
    public function getLastModifiedTimestampForSignatureOnce() : int;
}