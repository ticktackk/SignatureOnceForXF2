<?php

namespace TickTackk\SignatureOnce\Entity;

use TickTackk\SignatureOnce\Entity\Exception\CouldNotDetermineUserIdFromMessageException;
use TickTackk\SignatureOnce\Repository\Container as ContainerRepo;
use XF\Mvc\Entity\Repository;
use XF\Mvc\Entity\Structure as EntityStructure;
use XF\Phrase;

/**
 * @version 2.0.0 Alpha 1
 */
trait ContentTrait
{
    /**
     * @since 2.0.0 Alpha 1
     *
     * @return int
     */
    public function getUserIdForTckSignatureOnce() : int
    {
        $structure = $this->structure();
        $columns = $structure->columns;

        if (!array_key_exists('user_id', $columns))
        {
            throw new CouldNotDetermineUserIdFromMessageException();
        }

        return $this->user_id;
    }

    /**
     * @version 2.0.0 Alpha 1
     *
     * @param Phrase|null $error
     *
     * @return bool
     */
    public function canShowSignature(?Phrase &$error = null) : bool
    {
        if ($this->canBypassSignatureOnce($error))
        {
            return true;
        }

        $container = $this->getContainerForTckSignatureOnce();
        if (!$container)
        {
            return false;
        }

        if ($container->isSignatureShownOncePerContainerForTckSignatureOnce())
        {
            $containerFirstUserContent = $this->getContainerFirstUserContentForTckSignatureOnce();
            if (!$containerFirstUserContent) // no record found... so just assume it can be shown
            {
                return true;
            }

            if ($containerFirstUserContent->content_id !== $this->getEntityId()) // not the first content
            {
                return false;
            }

            return true; // is the first content
        }
        else if ($container->hasContentByUserBeforeContentInCurrentPageForTckSignatureOnce($this))
        {
            return false;
        }

        return true;
    }

    /**
     * @version 2.0.0 Alpha 1
     *
     * @param Phrase|null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(?Phrase &$error = null) : bool
    {
        $container = $this->getContainerForTckSignatureOnce();
        if (!$container)
        {
            return false;
        }

        return $container->canBypassSignatureOnce($error);
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     */
    protected function adjustContainerFirstUserContentRecordForTckSignatureOnce() : void
    {
        if (!$this->getUserIdForTckSignatureOnce())
        {
            return;
        }

        $this->getContainerRepoForTckSignatureOnce()->rebuildContainerFirstUserContentRecord(
            $this->getUserIdForTckSignatureOnce(),
            $this->getContainerForTckSignatureOnce(),
            $this->getEntityContentType()
        );
    }

    /**
     * @return Repository|ContainerRepo
     */
    protected function getContainerRepoForTckSignatureOnce() : ContainerRepo
    {
        return $this->repository('TickTackk\SignatureOnce:Container');
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param EntityStructure $structure
     * @param string $relationName
     * @param string $containerType
     * @param string $containerIdKey
     * @param string $userIdKey
     *
     * @return void
     */
    protected static function setupContainerEntityStructureForTckSignatureOnce(
        EntityStructure $structure,
        string $relationName,
        string $containerType,
        string $containerIdKey,
        string $userIdKey = 'user_id'
    ) : void
    {
        $structure->relations[$relationName] = [
            'entity' => 'TickTackk\SignatureOnce:ContainerFirstUserContent',
            'type' => static::TO_ONE,
            'conditions' => [
                ['user_id', '=', '$' . $userIdKey],
                ['container_type', '=', $containerType],
                ['container_id', '=', '$' . $containerIdKey],
                ['content_type', '=', $structure->contentType]
            ]
        ];

        if (array_key_exists('full', $structure->withAliases))
        {
            $structure->withAliases['full'][] = $relationName;
        }
        else
        {
            $structure->defaultWith[] = $relationName;
        }
    }
}