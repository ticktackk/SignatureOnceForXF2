<?php

namespace TickTackk\SignatureOnce\XF\Repository;

use XF\Mvc\Entity\ArrayCollection;

/**
 * Class Post
 *
 * @package TickTackk\SignatureOnce
 */
class Post extends XFCP_Post
{
    /**
     * @param \XF\Entity\Thread $thread
     * @param ArrayCollection|\TickTackk\SignatureOnce\XF\Entity\Post[]   $posts
     * @param                    $page
     * @param null|array         $postCounts
     *
     * @return ArrayCollection
     */
    public function setPostsShowSignature(\XF\Entity\Thread $thread, ArrayCollection $posts, $page, $postCounts = null)
    {
        if ($postCounts === null)
        {
            $postCounts = $this->getPostCountsForSignatureOnce($thread, $page);
        }

        foreach ($posts AS $postId => $post)
        {
            $showOncePerThread = $this->options()->showSignatureOncePerThread;
            if ($showOncePerThread)
            {
                $showSignature = !isset($postCounts[$postId]);
            }
            else
            {
                $showSignature = isset($postCounts[$postId]);
            }

            $posts[(int) $postId]->setShowSignature($showSignature);
        }

        return $posts;
    }

    /**
     * @param \XF\Entity\Thread $thread
     * @param                   $page
     *
     * @return array
     */
    public function getPostCountsForSignatureOnce(\XF\Entity\Thread $thread, $page)
    {
        $db = $this->app()->db();
        $perPage = $this->options()->messagesPerPage;

        $start = ($page - 1) * $perPage;
        $end = $start + $perPage;

        $messageStates = ['visible'];
        if ($thread->canViewDeletedPosts())
        {
            $messageStates[] = 'deleted';
        }
        if ($thread->canViewModeratedPosts())
        {
            $messageStates[] = 'moderated';
        }
        $messageStatesStr = $db->quote($messageStates);
        $oncePerContent = 'post_tmp.position < post.position AND';
        $groupBy = 'post_id';

        if (!$this->options()->showSignatureOncePerThread)
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
        ", [$thread->thread_id, $thread->thread_id, $start, $end, $perPage]);
    }
}