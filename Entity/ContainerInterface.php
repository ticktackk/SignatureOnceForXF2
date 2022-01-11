<?php

namespace TickTackk\SignatureOnce\Entity;

use XF\Db\AbstractAdapter as DbAdapter;
use XF\Mvc\Entity\Entity;
use XF\Phrase;
use TickTackk\SignatureOnce\Entity\ContentInterface as ContentEntityInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait as ContentEntityTrait;

/**
 * @version 2.0.0 Alpha 1
 */
interface ContainerInterface
{
    /**
     * @version 2.0.0 Alpha 1
     *
     * @param Phrase|null $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(
        ?Phrase &$error = null
    ) : bool;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return bool
     */
    public function isSignatureShownOncePerContainerForTckSignatureOnce() : bool;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param int $userId
     * @param string $contentType
     *
     * @return Entity|ContentInterface|null
     */
    public function getExpectedFirstContentForTckSignatureOnce(
        int $userId,
        string $contentType
    ) :? ContentEntityInterface;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param int $page
     *
     * @return void
     */
    public function setViewingPageForTckSignatureOnce(int $page) : void;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return int
     */
    public function getViewingPageForTckSignatureOnce() : int;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param array|<int, ContentEntityInterface|ContentEntityTrait|Entity> $contents
     *
     * @return void
     */
    public function setContentsFromCurrentPageForTckSignatureOnce(
        array $contents
    ) : void;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return array|<int, ContentEntityInterface|ContentEntityTrait|Entity>
     */
    public function getContentsFromCurrentPageForTckSignatureOnce() : array;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param int $page
     *
     * @return void
     */
    public function loadAllContentsFromCurrentPageForTckSignatureOnce(int $page) : void;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param ContentEntityInterface|ContentEntityTrait|Entity $content
     *
     * @return bool
     */
    public function hasContentByUserBeforeContentInCurrentPageForTckSignatureOnce(
        ContentEntityInterface $content
    ) : bool;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param DbAdapter $db
     *
     * @return void
     *
     * @throws \XF\Db\Exception
     */
    public function rebuildContainerFirstUserContentRecordsQueryForTckSignatureOnce(
        DbAdapter $db
    ) : void;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @return int
     */
    public function getContentsPerPageForTckSignatureOnce() : int;

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param array|<int, self|ContentEntityTrait|Entity> $contents
     *
     * @return int
     */
    public function getCurrentPageFromMessagesForTckSignatureOnce(array $contents) : int;
}