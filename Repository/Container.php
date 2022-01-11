<?php

namespace TickTackk\SignatureOnce\Repository;

use TickTackk\SignatureOnce\Entity\ContainerInterface as ContainerEntityInterface;
use TickTackk\SignatureOnce\Entity\ContainerTrait as ContainerEntityTrait;
use TickTackk\SignatureOnce\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait as ContentEntityTrait;
use TickTackk\SignatureOnce\Repository\Exception\NoForcedContentOrContentTypePassedException;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;

/**
 * @since 2.0.0 Alpha 1
 */
class Container extends Repository
{
    protected $firstContentByUsersOnContainerPage = [];

    protected function getFirstContentByUsersOnContainerPage() : array
    {
        return $this->firstContentByUsersOnContainerPage;
    }

    protected function setFirstContentByUsersOnContainerPage(array $firstContentByUsersOnContainerPage) : void
    {
        $this->firstContentByUsersOnContainerPage = $firstContentByUsersOnContainerPage;
    }

    /**
     * @param ContainerEntityInterface|ContainerEntityTrait|Entity $container
     * @param array|<int, ContentEntityInterface|ContentEntityTrait|Entity> $contents
     * @param int $page
     * @param bool $force
     *
     * @return void
     */
    protected function cacheFirstContentByUsersOnContainerPage(
        ContainerEntityInterface $container,
        array $contents,
        int $page,
        bool $force = false
    ) : void
    {
        $containerType = $container->getEntityContentType();
        $containerId = $container->getEntityId();
        $firstContentByUsersOnContainerPage = $this->getFirstContentByUsersOnContainerPage();

        if (!array_key_exists($containerType, $firstContentByUsersOnContainerPage))
        {
            $firstContentByUsersOnContainerPage[$containerType] = [];
        }

        if (!array_key_exists($containerId, $firstContentByUsersOnContainerPage[$containerType]))
        {
            $firstContentByUsersOnContainerPage[$containerType][$containerId] = [];
        }

        $this->setFirstContentByUsersOnContainerPage($firstContentByUsersOnContainerPage);
        foreach ($contents AS $content)
        {
            $this->cacheFirstContentByUserOnContainerPage($container, $content, $page, $force);
        }
    }

    /**
     * @param ContainerEntityInterface|ContentEntityTrait|Entity $container
     * @param ContentEntityInterface|ContentEntityTrait|Entity $content
     * @param int $page
     * @param bool $force
     *
     * @return void
     */
    protected function cacheFirstContentByUserOnContainerPage(
        ContainerEntityInterface $container,
        ContentEntityInterface $content,
        int $page,
        bool $force = false
    ) : void
    {
        $containerType = $container->getEntityContentType();
        $containerId = $container->getEntityId();

        $contentId = $content->getEntityId();
        $contentType = $content->getEntityContentType();
        $userId = $content->getUserIdForTckSignatureOnce();

        if (!isset($this->firstContentByUsersOnContainerPage[$containerType][$containerId][$contentType][$page][$userId]) || $force)
        {
            $this->firstContentByUsersOnContainerPage[$containerType][$containerId][$contentType][$page][$userId] = $contentId;
        }
    }

    /**
     * @param ContainerEntityInterface|ContentEntityTrait|Entity $container
     * @param ContentEntityInterface|ContentEntityTrait|Entity $content
     * @param array|<int, ContentEntityInterface|ContentEntityTrait|Entity> $contents
     * @param int $page
     *
     * @return int|null
     */
    public function getFirstUserContentIdFromContainerPage(
        ContainerEntityInterface $container,
        ContentEntityInterface $content,
        array $contents,
        int $page
    ) :? int
    {
        $this->cacheFirstContentByUsersOnContainerPage($container, $contents, $page);

        $userId = $content->getUserIdForTckSignatureOnce();
        if (!$userId)
        {
            return null;
        }

        $containerType = $container->getEntityContentType();
        $containerId = $container->getEntityId();

        return $this->firstContentByUsersOnContainerPage[$containerType][$containerId][$content->getEntityContentType()][$page][$userId] ?? null;
    }

    /**
     * @param Entity|ContainerEntityInterface $container
     *
     * @return void
     *
     * @throws \XF\Db\Exception
     */
    public function rebuildContainerFirstUserContentRecords(
        ContainerEntityInterface $container
    ) : void
    {
        $db = $this->db();

        $db->beginTransaction();

        $db->delete(
            'xf_tck_signature_once_container_first_user_content',
            'container_type = ? AND container_id = ?',
            [$container->getEntityContentType(), $container->getEntityId()]
        );

        $container->rebuildContainerFirstUserContentRecordsQueryForTckSignatureOnce($db);

        $db->commit();
    }

    /**
     * @param int $userId
     * @param Entity|ContainerEntityInterface $container
     * @param string $contentType
     * @param Entity|ContentEntityInterface|null $forceContent
     *
     * @return void
     */
    public function rebuildContainerFirstUserContentRecord(
        int $userId,
        ContainerEntityInterface $container,
        string $contentType,
        ?ContentEntityInterface $forceContent = null
    ) : void
    {
        if (!$forceContent && !$contentType)
        {
            throw new NoForcedContentOrContentTypePassedException();
        }

        if ($forceContent)
        {
            $content = $forceContent;
        }
        else
        {
            $content = $container->getExpectedFirstContentForTckSignatureOnce($userId, $contentType);
        }

        $db = $this->db();

        $existingValue = $db->fetchRow("
            SELECT first_user_content.record_id,
                   first_user_content.content_date
            FROM xf_tck_signature_once_container_first_user_content AS first_user_content
            WHERE first_user_content.container_type = ?
              AND first_user_content.container_id = ?
              AND first_user_content.content_type = ?
              AND first_user_content.user_id = ?
        ", [$container->getEntityContentType(), $container->getEntityId(), $contentType, $userId]);

        if ($content)
        {
            if ($existingValue)
            {
                if ((int) $existingValue['content_date'] < (int) $content->getContentDateForTckSignatureOnce())
                {
                    $db->update('xf_tck_signature_once_container_first_user_content', [
                        'content_id' => $content->getEntityId(),
                        'content_date' => $content->getContentDateForTckSignatureOnce()
                    ], 'record_id = ?', $existingValue['record_id']);
                }
            }
            else
            {
                $db->insert('xf_tck_signature_once_container_first_user_content', [
                    'user_id' => $userId,
                    'container_type' => $container->getEntityContentType(),
                    'container_id' => $container->getEntityId(),
                    'content_type' => $contentType,
                    'content_id' => $content->getEntityId(),
                    'content_date' => $content->getContentDateForTckSignatureOnce()
                ]);
            }
        }
        else
        {
            if ($existingValue)
            {
                $db->delete(
                    'xf_tck_signature_once_container_first_user_content',
                    'record_id = ?',
                    $existingValue['record_id']
                );
            }
        }
    }
}