<?php

namespace TickTackk\SignatureOnce\Entity;

use TickTackk\SignatureOnce\Entity\Exception\CouldNotDetermineUserIdFromMessageException;
use XF\Mvc\Entity\Structure as EntityStructure;
use XF\Phrase;

/**
 * Trait ContentTrait
 *
 * @package TickTackk\SignatureOnce\Entity
 */
trait ContentTrait
{
    /**
     * @var bool
     */
    protected $showSignature = true;

    /**
     * @return bool
     */
    protected function getShowSignature() : bool
    {
        return $this->showSignature;
    }

    /**
     * @param bool $showSignature
     */
    public function setShowSignature(bool $showSignature = true) : void
    {
        $this->showSignature = $showSignature;
    }

    /**
     * @return string
     */
    abstract protected function getContainerRelationNameForTckSignatureOnce() : string;

    /**
     * @return int
     */
    public function getUserIdForTckSignatureOnce() : int
    {
        /** @var EntityStructure $structure */
        $structure = $this->structure();
        $columns = $structure->columns;

        if (!\array_key_exists('user_id', $columns))
        {
            throw new CouldNotDetermineUserIdFromMessageException();
        }

        return $this->user_id;
    }

    /**
     * @param Phrase|null $error
     *
     * @return bool
     */
    public function canShowSignature(Phrase &$error = null) : bool
    {
        $containerRelationName = $this->getContainerRelationNameForTckSignatureOnce();
        $container = $this->getRelation($containerRelationName);
        if (!$container)
        {
            return false;
        }

        if ($this->canBypassSignatureOnce($error))
        {
            return true;
        }

        return $this->getShowSignature();
    }

    /**
     * @param Phrase|null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(Phrase &$error = null) : bool
    {
        $containerRelationName = $this->getContainerRelationNameForTckSignatureOnce();
        $container = $this->getRelation($containerRelationName);
        if (!$container)
        {
            return false;
        }

        return $container->canBypassSignatureOnce($error);
    }
}