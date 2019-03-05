<?php

namespace TickTackk\SignatureOnce\Entity;

/**
 * Trait ContentTrait
 *
 * @package TickTackk\SignatureOnce\Entity
 */
trait ContentTrait
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
}