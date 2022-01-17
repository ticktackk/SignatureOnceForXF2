<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Entity\ContainerFirstUserContent as ContainerFirstUserContentEntity;
use TickTackk\SignatureOnce\Entity\ContentTrait as ContentEntityTrait;
use TickTackk\SignatureOnce\Entity\SignatureOnceTrait;
use TickTackk\SignatureOnce\XF\Entity\Thread as ExtendedThreadEntity;
use XF\Mvc\Entity\Structure as EntityStructure;

/**
 * @version 2.0.0 Alpha 1
 *
 * RELATIONS
 * @property ExtendedThreadEntity Thread
 * @property ContainerFirstUserContentEntity ThreadFirstUserPost
 */
class Post extends XFCP_Post
{
    use ContentEntityTrait, SignatureOnceTrait;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function postInsertedVisible()
    {
        parent::postInsertedVisible();

        $this->getHandlerForTckSignatureOnce('post')->adjustContainerFirstUserContentRecord($this);
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function postMadeVisible()
    {
        parent::postMadeVisible();

        $this->getHandlerForTckSignatureOnce('post')->adjustContainerFirstUserContentRecord($this);
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param bool $hardDelete
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function postHidden($hardDelete = false)
    {
        parent::postHidden($hardDelete);

        $this->getHandlerForTckSignatureOnce('post')->adjustContainerFirstUserContentRecord($this);
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