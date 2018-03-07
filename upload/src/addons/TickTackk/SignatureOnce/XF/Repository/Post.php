<?php

namespace TickTackk\SignatureOnce\XF\Repository;

use XF\Entity\Thread;

class Post extends XFCP_Post
{
    protected $threadPostCache = [];

    public function updateCachedPostsForThreadForSignatureOnce(Thread $thread, array $threadPostCacheData)
    {
        $this->threadPostCache[$thread->thread_id] = $threadPostCacheData;
    }

    /**
     * @param Thread $thread
     * @param \XF\Entity\Post $currentPost
     *
     * @return array
     */
    public function getCachedPostsForThreadForSignatureOnce(Thread $thread, \XF\Entity\Post $currentPost)
    {
        $options = $this->app()->options();
        $showSignatureOncePerThread = $options->showSignatureOncePerThread;

        $messagesPerPage = $options->messagesPerPage;
        $currentPage = floor($currentPost->position / $messagesPerPage) + 1;

        if (empty($this->threadPostCache))
        {
            $finder = $this->finder('XF:Post');
            $postsInThread = $finder->inThread($thread);

            if (!$showSignatureOncePerThread)
            {
                $postsInThread = $postsInThread->onPage($currentPage);
            }

            $postsInThread = $postsInThread
                ->order('position', 'ASC') // asc because older posts show first
                ->fetchColumns(['user_id', 'post_id', 'position']);

            foreach ($postsInThread as $postInThread)
            {
                $postInPage = floor($postInThread['position'] / $messagesPerPage) + 1;
                if (empty($this->threadPostCache[$thread->thread_id][$postInThread['user_id']]['pages'][$postInPage]['postCount']))
                {
                    $this->threadPostCache[$thread->thread_id][$postInThread['user_id']]['pages'][$postInPage]['postCount'] = 1;
                }
                else
                {
                    $this->threadPostCache[$thread->thread_id][$postInThread['user_id']]['pages'][$postInPage]['postCount']++;
                }
                $this->threadPostCache[$thread->thread_id][$postInThread['user_id']]['pages'][$postInPage]['posts'][$postInThread['post_id']] = [
                    'postId' => $postInThread['post_id'],
                    'signatureShown' => false
                ];
            }
        }

        return [$currentPage, $showSignatureOncePerThread, $this->threadPostCache[$thread->thread_id]];
    }
}