<?php

namespace TickTackk\SignatureOnce\XF\Repository;

use TickTackk\SignatureOnce\Entity\ContainerInterface;
use TickTackk\SignatureOnce\Entity\ContentInterface as EntityContentInterface;
use TickTackk\SignatureOnce\Entity\ContentTrait as EntityContentTrait;
use TickTackk\SignatureOnce\Repository\ContentInterface;
use TickTackk\SignatureOnce\Repository\ContentTrait;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;

/**
 * Class Post
 *
 * @package TickTackk\SignatureOnce
 */
class Post extends XFCP_Post implements ContentInterface
{
    use ContentTrait;

    /**
     * @param Entity|ContainerInterface                                     $container
     * @param ArrayCollection|EntityContentTrait[]|EntityContentInterface[] $messages
     * @param int                                                           $page
     *
     * @return array
     */
    public function getMessageCountsForSignatureOnce(/** @noinspection PhpUnusedParameterInspection */
        ContainerInterface $container, ArrayCollection $messages, $page)
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
        $containerId = $db->quote($container->thread_id);
        $startQuoted = $db->quote($start);
        $endQuoted = $db->quote($end);

        if ($this->showSignatureOncePerPage())
        {
            $pageCondition = "AND post_tmp.position >= {$startQuoted}";
        }
        else
        {
            $pageCondition = 'AND post_tmp.position < post_main.position';
        }

        return $db->fetchPairs("
            SELECT
                post_main.post_id,
                (
                  SELECT post_id 
                  FROM xf_post AS post_tmp
                  WHERE post_tmp.user_id = post_main.user_id
                    {$pageCondition}
                    AND post_tmp.position < post_main.position
                    AND post_tmp.thread_id = {$containerId}
                    AND post_tmp.message_state IN ({$messageStatesStr})
                  ORDER BY post_tmp.post_id ASC
                  LIMIT 1
                ) AS previous_post_id
            FROM
            (
                SELECT DISTINCT user_id, post_id, position
                FROM xf_post AS post
                WHERE post.thread_id = {$containerId}
                  AND post.message_state IN ({$messageStatesStr})
                  AND post.position >= {$startQuoted}
                  AND post.position < {$endQuoted}
            ) AS post_main
        ");
    }

    /**
     * @return bool
     */
    public function showSignatureOncePerPage()
    {
        return !$this->options()->showSignatureOncePerThread;
    }
}