<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerInterface as ContainerEntityInterface;
use TickTackk\SignatureOnce\Entity\ContainerTrait as ContainerEntityTrait;
use TickTackk\SignatureOnce\Entity\ContentInterface as ContentEntityInterface;
use XF\Db\AbstractAdapter as DbAdapter;
use XF\Phrase;
use XF\Repository\Post as PostRepo;
use TickTackk\SignatureOnce\XF\Entity\Post as ExtendedPostEntity;

/**
 * @version 2.0.0 Alpha 1
 */
class Thread extends XFCP_Thread implements ContainerEntityInterface
{
    use ContainerEntityTrait;

    /**
     * @inheritDoc
     */
    public function canBypassSignatureOnce(Phrase &$error = null) : bool
    {
        $visitor = \XF::visitor();
        return $visitor->hasNodePermission($this->node_id, 'bypassSignatureOnce');
    }

    /**
     * @inheritDoc
     */
    public function isSignatureShownOncePerContainerForTckSignatureOnce(): bool
    {
        return $this->app()->options()->showSignatureOncePerThread;
    }

    /**
     * @inheritDoc
     */
    public function loadAllContentsFromCurrentPageForTckSignatureOnce(int $page) : void
    {
        /** @var PostRepo $postRepo */
        $postRepo = $this->repository('XF:Post');
        $contents = $postRepo->findPostsForThreadView($this)
            ->onPage($page, $this->getContentsPerPageForTckSignatureOnce())
            ->fetch()
            ->toArray();

        $this->setContentsFromCurrentPageForTckSignatureOnce($contents);
    }

    /**
     * @inheritDoc
     */
    public function getExpectedFirstContentForTckSignatureOnce(
        int $userId,
        string $contentType
    ): ?ContentEntityInterface
    {
        /** @var PostRepo $postRepo */
        $postRepo = $this->repository('XF:Post');

        /** @var ExtendedPostEntity $post */
        $post = $postRepo->findPostsForThreadView($this, ['visibility' => false])
            ->where('user_id', $userId)
            ->orderByDate()
            ->fetchOne();

        return $post;
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        $this->containerPostDeleteForTckSignatureOnce();
    }

    /**
     * @inheritDoc
     */
    public function rebuildContainerFirstUserContentRecordsQueryForTckSignatureOnce(DbAdapter $db): void
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
		", [$this->getEntityContentType(), 'post', $this->getEntityId()]);
    }

    /**
     * @inheritDoc
     */
    public function getContentsPerPageForTckSignatureOnce(): int
    {
        return $this->app()->options()->messagesPerPage;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentPageFromMessagesForTckSignatureOnce(
        array $contents
    ): int
    {
        $lastPosition = \max(\array_column($contents, 'position')) + 1;

        return (int) \max(1, \ceil($lastPosition / $this->getContentsPerPageForTckSignatureOnce()));
    }
}