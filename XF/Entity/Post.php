<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerFirstUserContent as ContainerFirstUserContentEntity;
use TickTackk\SignatureOnce\Entity\ContainerInterface as ContainerEntityInterface;
use TickTackk\SignatureOnce\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait as ContentEntityTrait;
use TickTackk\SignatureOnce\XF\Entity\Thread as ExtendedThreadEntity;
use XF\Mvc\Entity\Structure as EntityStructure;

/**
 * @version 2.0.0 Alpha 1
 *
 * RELATIONS
 * @property ExtendedThreadEntity Thread
 * @property ContainerFirstUserContentEntity ThreadFirstUserPost
 */
class Post extends XFCP_Post implements ContentEntityInterface
{
    use ContentEntityTrait;

    /**
     * @inheritDoc
     */
    public function getContainerTypeForTckSignatureOnce() : string
    {
        return 'thread';
    }

    /**
     * @inheritDoc
     */
    public function getContainerForTckSignatureOnce(): ?ContainerEntityInterface
    {
        return $this->Thread;
    }

    /**
     * @inheritDoc
     */
    public function getContainerFirstUserContentForTckSignatureOnce(): ?ContainerFirstUserContentEntity
    {
        return $this->ThreadFirstUserPost;
    }

    /**
     * @inheritDoc
     */
    public function getContainerIdFroTckSignatureOnce(): int
    {
        return $this->thread_id;
    }

    /**
     * @inheritDoc
     */
    public function getContentDateForTckSignatureOnce(): int
    {
        return $this->post_date;
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     */
    protected function postInsertedVisible()
    {
        parent::postInsertedVisible();

        $this->adjustContainerFirstUserContentRecordForTckSignatureOnce();
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     */
    protected function postMadeVisible()
    {
        parent::postMadeVisible();

        $this->adjustContainerFirstUserContentRecordForTckSignatureOnce();
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param bool $hardDelete
     *
     * @return void
     */
    protected function postHidden($hardDelete = false)
    {
        parent::postHidden($hardDelete);

        $this->adjustContainerFirstUserContentRecordForTckSignatureOnce(false);
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param EntityStructure $structure
     *
     * @return EntityStructure
     *
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function getStructure(EntityStructure $structure)
    {
        $structure = parent::getStructure($structure);

        static::setupContainerEntityStructureForTckSignatureOnce(
            $structure,
            'ThreadFirstUserPost',
            'thread',
            'thread_id'
        );

        return $structure;
    }
}