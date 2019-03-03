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
     * @var string
     */
    protected $columnPrefixForPostData = 'post_';

    /**
     * @param ArrayCollection|\TickTackk\SignatureOnce\XF\Entity\Post[] $posts
     * @param int             $page
     * @param null            $postCounts
     *
     * @return ArrayCollection
     */
    public function setPostsShowSignature(ArrayCollection $posts, $page, $postCounts = null)
    {
        if ($postCounts === null)
        {
            $postCounts = $this->getPostCountsForSignatureOnce($posts, $page);
        }

        foreach ($postCounts AS $postId => $postCount)
        {
            $postIdInt = (int) utf8_substr($postId, utf8_strlen($this->columnPrefixForPostData));
            if (isset($posts[$postIdInt]))
            {
                $posts[$postIdInt]->setShowSignature($postCount === 0);
            }
        }

        return $posts;
    }

    /**
     * @param ArrayCollection|\TickTackk\SignatureOnce\XF\Entity\Post[] $posts
     * @param int             $page
     *
     * @return array|bool|false
     */
    public function getPostCountsForSignatureOnce(ArrayCollection $posts, $page)
    {
        $db = $this->app()->db();
        $perThread = $this->options()->showSignatureOncePerThread;
        $perPage = $this->options()->messagesPerPage;

        $queries = [];
        foreach ($posts AS $post)
        {
            if ($thread = $post->Thread)
            {
                /** @var \XF\Finder\Post $postFinder */
                $postFinder = $this->finder('XF:Post');
                $postFinder
                    ->inThread($thread)
                    ->applyVisibilityChecksInThread($thread)
                    ->where('user_id', $post->user_id)
                    ->where('position', '<', $post->position);

                if (!$perThread)
                {
                    $postFinder->whereSql('(FLOOR(' . $postFinder->columnSqlName('position') . "/{$perPage}) + 1) >= {$page}");
                }

                $queries[] = "({$postFinder->getQuery(['countOnly' => true])}) AS {$this->columnPrefixForPostData}{$post->post_id}";
            }
        }

        return $db->fetchRow('SELECT ' . implode(', ', $queries) . ' FROM xf_post LIMIT 1');
    }
}