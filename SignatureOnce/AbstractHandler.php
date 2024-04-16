<?php

namespace TickTackk\SignatureOnce\SignatureOnce;

use TickTackk\SignatureOnce\Entity\ContainerFirstUserContent as ContainerFirstUserContentEntity;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Phrase;
use XF\App as BaseApp;
use XF\Db\AbstractAdapter as DbAdapter;

/**
 * @since 2.0.0
 * @version 2.0.6
 */
abstract class AbstractHandler
{
    protected string $contentType;

    protected ?int $page = null;

    protected ?array $contents = null;

    protected ?array $userFirstContentIdMap = null;

    public function __construct(
        string $contentType
    )
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function setPage(
        int $page
    ) : self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPage():? int
    {
        return $this->page;
    }

    /**
     * @param Entity[] $contents
     *
     * @return $this
     */
    public function setContents(array $contents) : self
    {
        $this->contents = $contents;

        return $this;
    }

    /**
     * @return Entity[]|null
     */
    public function getContents():? array
    {
        return $this->contents;
    }

    /**
     * @since 2.0.6
     *
     * @return bool
     */
    public function hasContents() : bool
    {
        return !empty($this->contents);
    }

    /**
     * @return array|null
     */
    public function getUserFirstContentIdMap():? array
    {
        return $this->userFirstContentIdMap;
    }

    /**
     * @param Entity $content
     * @param Phrase|null $error
     *
     * @return bool
     */
    abstract public function canBypassSignatureOnce(
        Entity $content,
        ?Phrase &$error = null
    ) : bool;

    /**
     * @param Entity $container
     *
     * @return bool
     */
    abstract public function isSignatureShownOncePerPage(
        Entity $container
    ) : bool;

    /**
     * @param Entity $content
     *
     * @return Entity|null
     */
    abstract protected function getContainerFromContent(
        Entity $content
    ) :? Entity;

    /**
     * @param Entity $content
     *
     * @return ContainerFirstUserContentEntity|null
     */
    abstract protected function getFirstUserContentRecord(
        Entity $content
    ) :? ContainerFirstUserContentEntity;

    /**
     * @param Entity $content
     *
     * @return int
     */
    abstract protected function getUserIdFromContent(
        Entity $content
    ) : int;

    /**
     * @param Entity $content
     *
     * @return int
     */
    abstract protected function getDateFromContent(
        Entity $content
    ) : int;

    /**
     * @param int $userId
     * @param Entity $container
     *
     * @return Entity|null
     */
    abstract protected function getExpectedFirstContent(
        int $userId,
        Entity $container
    ) :? Entity;

    /**
     * @param Entity $container
     *
     * @return int
     */
    abstract protected function getContentsPerPage(
        Entity $container
    ) : int;

    /**
     * @param Entity $container
     *
     * @return int
     */
    abstract public function getCalculatedPageFromContents(
        Entity $container
    ) : int;

    /**
     * @param DbAdapter $db
     * @param string $containerType
     * @param string $containerId
     *
     * @return void
     *
     * @throws \XF\Db\Exception
     */
    abstract protected function internalRebuildContainerFirstUserContentRecords(
        DbAdapter $db,
        string $containerType,
        string $containerId
    ) : void;

    /**
     * @param Entity $container
     *
     * @return void
     */
    abstract public function loadAllContents(
        Entity $container
    ) : void;

    /**
     * @param Entity $content
     * @param Phrase|null $error
     *
     * @return bool
     */
    public function canShowSignature(
        Entity $content,
        ?Phrase &$error = null
    ) : bool
    {
        if ($this->canBypassSignatureOnce($content, $error))
        {
            return true;
        }

        $container = $this->getContainerFromContent($content);
        if (!$container)
        {
            return false;
        }

        if ($this->isSignatureShownOncePerPage($content))
        {
            return $this->hasContentOwnerAddedContentBefore($content);
        }

        $containerFirstUserContent = $this->getFirstUserContentRecord($content);
        if (!$containerFirstUserContent)
        {
            return true;
        }

        if ($containerFirstUserContent->content_id !== $content->getEntityId())
        {
            return false;
        }

        return true;
    }

    /**
     * @version 2.0.6
     *
     * @return void
     */
    protected function cacheUserFirstContentIdMap() : void
    {
        $userFirstContentIdMap = $this->userFirstContentIdMap;
        if ($userFirstContentIdMap !== null)
        {
            return;
        }

        $userFirstContentIdMap = [];
        if ($this->hasContents())
        {
            foreach ($this->getContents() AS $content)
            {
                $userId = $this->getUserIdFromContent($content);
                if (!$userId || array_key_exists($userId, $userFirstContentIdMap))
                {
                    continue;
                }

                $userFirstContentIdMap[$userId] = $content->getEntityId();
            }
        }
        $this->userFirstContentIdMap = $userFirstContentIdMap;
    }

    /**
     * @param Entity $content
     *
     * @return bool
     */
    public function hasContentOwnerAddedContentBefore(
        Entity $content
    ) : bool
    {
        $this->cacheUserFirstContentIdMap();

        $userId = $this->getUserIdFromContent($content);
        if (!$userId)
        {
            return false;
        }

        $userFirstContentIdMap = $this->getUserFirstContentIdMap();
        if (!$userFirstContentIdMap || !array_key_exists($userId, $userFirstContentIdMap))
        {
            return false;
        }

        return $content->getEntityId() === $userFirstContentIdMap[$userId];
    }

    /**
     * @version 2.0.6
     *
     * @param Entity $container
     *
     * @return int
     */
    public function hasLoadedAllContents(
        Entity $container
    ) : int
    {
        if (!$this->hasContents())
        {
            return false;
        }

        return count($this->getContents()) === $this->getContentsPerPage($container);
    }

    /**
     * @param string $containerType
     * @param string $containerId
     *
     * @return void
     *
     * @throws \XF\Db\Exception
     */
    public function rebuildContainerFirstUserContentRecords(
        string $containerType,
        string $containerId
    ) : void
    {
        $db = $this->db();
        $db->beginTransaction();

        $db->delete(
            'xf_tck_signature_once_container_first_user_content',
            'container_type = ? AND container_id = ? AND content_type = ?',
            [$containerType, $containerId, $this->getContentType()]
        );

        $this->internalRebuildContainerFirstUserContentRecords(
            $db,
            $containerType,
            $containerId
        );

        $db->commit();
    }

    /**
     * @param int $userId
     * @param Entity $container
     *
     * @return void
     */
    public function rebuildContainerFirstUserContentRecord(
        int $userId,
        Entity $container
    ) : void
    {
        $expectedFirstContent = $this->getExpectedFirstContent($userId, $container);
        $containerType = $container->getEntityContentType();
        $containerId = $container->getEntityId();
        $contentType = $this->getContentType();

        $db = $this->db();

        if ($expectedFirstContent)
        {
            $db->insert('xf_tck_signature_once_container_first_user_content', [
                'user_id' => $userId,
                'container_type' => $containerType,
                'container_id' => $containerId,
                'content_type' => $contentType,
                'content_id' => $expectedFirstContent->getEntityId(),
                'content_date' => $this->getDateFromContent($expectedFirstContent)
            ], false, 'content_id = VALUES(content_id), content_date = VALUES(content_date)');
        }
        else
        {
            $db->delete(
                'xf_tck_signature_once_container_first_user_content',
                'container_type = ? AND container_id = ? AND content_type = ? AND user_id = ?',
                [$containerType, $containerId, $contentType, $userId]
            );
        }
    }

    public function adjustContainerFirstUserContentRecord(Entity $content) : void
    {
        $userId = $this->getUserIdFromContent($content);
        $container = $this->getContainerFromContent($content);
        if (!$userId)
        {
            return;
        }

        $this->rebuildContainerFirstUserContentRecord($userId, $container);
    }

    /**
     * @param Entity $container
     *
     * @return void
     */
    public function containerPostDelete(Entity $container) : void
    {
        $this->db()->delete(
            'xf_tck_signature_once_container_first_user_content',
            'container_type = ? AND container_id = ?',
            [$container->getEntityContentType(), $container->getEntityId()]
        );
    }

    protected function app() : BaseApp
    {
        return \XF::app();
    }

    protected function db() : DbAdapter
    {
        return $this->app()->db();
    }

    protected function options() : \ArrayObject
    {
        return $this->app()->options();
    }

    protected function repository(string $identifier) : Repository
    {
        return $this->app()->repository($identifier);
    }
}