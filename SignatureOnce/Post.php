<?php

namespace TickTackk\SignatureOnce\SignatureOnce;

use TickTackk\SignatureOnce\Entity\ContainerFirstUserContent as ContainerFirstUserContentEntity;
use XF\Db\AbstractAdapter as DbAdapter;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Phrase;
use XF\Repository\Post as PostRepo;
use TickTackk\SignatureOnce\XF\Entity\Post as ExtendedPostEntity;
use TickTackk\SignatureOnce\XF\Entity\Thread as ExtendedThreadEntity;

/**
 * @since 2.0.0
 */
class Post extends AbstractHandler
{
    /**
     * @inheritDoc
     *
     * @param ExtendedPostEntity $content
     */
    public function canBypassSignatureOnce(
        Entity $content,
        ?Phrase &$error = null
    ): bool
    {
        $thread = $content->Thread;
        if (!$thread)
        {
            return false;
        }

        return \XF::visitor()->hasNodePermission($thread->node_id, 'bypassSignatureOnce');
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedThreadEntity $container
     */
    public function isSignatureShownOncePerPage(Entity $container): bool
    {
        return !$this->options()->tckSignatureOnceShowSignatureOncePerThread;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedPostEntity $content
     */
    protected function getContainerFromContent(Entity $content): ?Entity
    {
        return $content->Thread;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedPostEntity $content
     */
    protected function getFirstUserContentRecord(Entity $content): ?ContainerFirstUserContentEntity
    {
        return $content->ThreadFirstUserPost;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedPostEntity $content
     */
    protected function getUserIdFromContent(Entity $content): int
    {
        return $content->user_id;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedPostEntity $content
     */
    protected function getDateFromContent(Entity $content): int
    {
        return $content->post_date;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedThreadEntity $container
     */
    protected function getExpectedFirstContent(
        int $userId,
        Entity $container
    ): ?Entity
    {
        return $this->getPostRepo()->findPostsForThreadView($container, ['visibility' => false])
            ->where('user_id', $userId)
            ->orderByDate()
            ->fetchOne();
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedThreadEntity $container
     */
    protected function getContentsPerPage(Entity $container): int
    {
        return (int) $this->options()->messagesPerPage;
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedThreadEntity $container
     */
    public function getCalculatedPageFromContents(Entity $container): int
    {
        $lastPosition = max(array_column($this->getContents(), 'position')) + 1;

        return (int) max(1, ceil($lastPosition / $this->getContentsPerPage($container)));
    }

    /**
     * @inheritDoc
     */
    protected function internalRebuildContainerFirstUserContentRecords(
        DbAdapter $db,
        string $containerType,
        string $containerId
    ): void
    {
        $db->query("
			INSERT INTO xf_tck_signature_once_container_first_user_content
			    (user_id, container_type, container_id, content_type, content_id, content_date)
			    SELECT user_id, ?, thread_id, ?, post_id, post_date
			    FROM xf_post
			    WHERE thread_id = ?
				  AND message_state = 'visible'
				  AND user_id > 0
			    GROUP BY user_id ASC
		", [$containerType, $this->getContentType(), $containerId]);
    }

    /**
     * @inheritDoc
     *
     * @param ExtendedThreadEntity $container
     */
    public function loadAllContents(
        Entity $container
    ): void
    {
        $page = $this->getPage();
        $perPage = $this->getContentsPerPage($container);
        $postFinder = $this->getPostRepo()->findPostsForThreadView($container)->onPage($page, $perPage);

        $this->setContents($postFinder->fetch()->toArray());
    }

    /**
     * @return Repository|PostRepo
     */
    protected function getPostRepo() : PostRepo
    {
        return $this->repository('XF:Post');
    }
}