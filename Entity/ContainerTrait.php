<?php

namespace TickTackk\SignatureOnce\Entity;

use TickTackk\SignatureOnce\Entity\Exception\NoContentsFromCurrentPageSetException;
use TickTackk\SignatureOnce\Entity\Exception\NoViewingPageSetException;
use TickTackk\SignatureOnce\Repository\Container as ContainerRepo;
use XF\Mvc\Entity\Repository;
use TickTackk\SignatureOnce\Entity\ContentInterface as ContentEntityInterface;

/**
 * @since 2.0.0 Alpha 1
 */
trait ContainerTrait
{
    /**
     * @var null|array
     */
    protected $contentsFromCurrentPageForTckSignatureOnce = null;

    /**
     * @var null|int
     */
    protected $viewingPageForTckSignatureOnce = null;

    /**
     * @inheritDoc
     */
    public function setViewingPageForTckSignatureOnce(int $page) : void
    {
        $this->viewingPageForTckSignatureOnce = $page;
    }

    /**
     * @inheritDoc
     */
    public function getViewingPageForTckSignatureOnce() : int
    {
        if ($this->viewingPageForTckSignatureOnce === null)
        {
            throw new NoViewingPageSetException();
        }

        return $this->viewingPageForTckSignatureOnce;
    }

    /**
     * @inheritDoc
     */
    public function setContentsFromCurrentPageForTckSignatureOnce(array $contents): void
    {
        $this->contentsFromCurrentPageForTckSignatureOnce = $contents;
    }

    /**
     * @inheritDoc
     */
    public function getContentsFromCurrentPageForTckSignatureOnce(): array
    {
        $contentsFromCurrentPageForTckSignatureOnce = $this->contentsFromCurrentPageForTckSignatureOnce;
        if ($contentsFromCurrentPageForTckSignatureOnce === null)
        {
            throw new NoContentsFromCurrentPageSetException();
        }

        return $contentsFromCurrentPageForTckSignatureOnce;
    }

    /**
     * @inheritDoc
     */
    public function hasContentByUserBeforeContentInCurrentPageForTckSignatureOnce(ContentEntityInterface $content): bool
    {
        $firstContentId = $this->getContainerRepoForTckSignatureOnce()->getFirstUserContentIdFromContainerPage(
            $this,
            $content,
            $this->getContentsFromCurrentPageForTckSignatureOnce(),
            $this->getViewingPageForTckSignatureOnce()
        );
        if ($firstContentId === null)
        {
            return false;
        }

        return $firstContentId !== $content->getEntityId();
    }

    protected function containerPostDeleteForTckSignatureOnce() : void
    {
        $this->db()->delete(
            'xf_tck_signature_once_container_first_user_content',
            'container_type = ? AND container_id = ?',
            [$this->getEntityContentType(), $this->getEntityId()]
        );
    }

    /**
     * @return Repository|ContainerRepo
     */
    protected function getContainerRepoForTckSignatureOnce() : ContainerRepo
    {
        return $this->repository('TickTackk\SignatureOnce:Container');
    }
}