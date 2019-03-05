<?php

namespace TickTackk\SignatureOnce\XF\Repository;

use TickTackk\SignatureOnce\Entity\ContainerInterface;
use TickTackk\SignatureOnce\Repository\ContentInterface;
use TickTackk\SignatureOnce\Repository\ContentTrait;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
use TickTackk\SignatureOnce\Entity\ContentTrait as EntityContentTrait;
use TickTackk\SignatureOnce\Entity\ContentInterface as EntityContentInterface;

/**
 * Class Post
 *
 * @package TickTackk\SignatureOnce
 */
class Post extends XFCP_Post implements ContentInterface
{
    use ContentTrait;

    /**
     * @return bool
     */
    public function showSignatureOncePerPage()
    {
        return !$this->options()->showSignatureOncePerThread;
    }

    /**
     * @param Entity|ContainerInterface                                     $container
     * @param ArrayCollection|EntityContentTrait[]|EntityContentInterface[] $messages
     * @param int                                                           $page
     *
     * @return array
     */
    public function getMessageCountsForSignatureOnce(/** @noinspection PhpUnusedParameterInspection */ ContainerInterface $container, ArrayCollection $messages, $page)
    {
        $db = $this->app()->db();

        $perPage = $this->options()->messagesPerPage;
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage;
        $messageStates = ['visible'];

        /** @var \TickTackk\SignatureOnce\XF\Entity\Thread $container */
        if ($container->canViewDeletedPosts())
        {
            $messageStates[] = 'deleted';
        }
        if ($container->canViewModeratedPosts())
        {
            $messageStates[] = 'moderated';
        }
        $messageStatesStr = $db->quote($messageStates);
        $oncePerContent = 'post_tmp.position < post.position AND';
        $groupBy = 'post_id';

        if ($this->showSignatureOncePerPage())
        {
            $oncePerContent = '';
            $groupBy = 'user_id';
        }

        return $db->fetchPairs("
            SELECT post.post_id, COUNT(*)
            FROM xf_post AS post
            INNER JOIN xf_post AS post_tmp ON 
            (
                  {$oncePerContent}
                  post_tmp.user_id = post.user_id AND
                  post_tmp.thread_id = post.thread_id
            )
            WHERE post.thread_id = ?
              AND post_tmp.thread_id = ?
              AND post.message_state IN ({$messageStatesStr})
              AND post_tmp.message_state IN ({$messageStatesStr})
              AND post.position >= ?
              AND post.position < ?
            GROUP BY post.{$groupBy}
            ORDER BY post_id ASC
            LIMIT ?
        ", [$container->thread_id, $container->thread_id, $start, $end, $perPage]);
    }
}