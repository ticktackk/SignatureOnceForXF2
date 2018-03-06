<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use TickTackk\SignatureOnce\Listener;

class Post extends XFCP_Post
{
    public function canBypassSignatureOnce(/** @noinspection PhpUnusedParameterInspection */
        &$error = null)
    {
        $thread = $this->Thread;
        $visitor = \XF::visitor();

        $nodeId = $thread->node_id;

        return $visitor->hasNodePermission($nodeId, 'bypassSignatureOnce');
    }

    public function canShowSignature()
    {
        if ($this->canBypassSignatureOnce())
        {
            return true;
        }

        $options = $this->app()->options();
        $showSignatureOncePerThread = $options->showSignatureOncePerThread;
        $messagesPerPage = $options->messagesPerPage;
        $currentPage = floor($this->position / $messagesPerPage) + 1;

        if (empty(Listener::$threadPostsData[$this->thread_id][$this->user_id]['pages']))
        {
            $finder = $this->finder('XF:Post');
            $postsInThread = $finder->inThread($this->Thread);

            if ($showSignatureOncePerThread)
            {
                $postsInThread = $postsInThread->onPage($currentPage);
            }

            $postsInThread = $postsInThread
                ->order('position', 'ASC') // asc because older posts show first
                ->fetchColumns(['user_id', 'post_id', 'position']);

            foreach ($postsInThread as $postInThread)
            {
                $postInPage = floor($postInThread['position'] / $messagesPerPage) + 1;
                if (empty(Listener::$threadPostsData[$this->thread_id][$postInThread['user_id']]['pages'][$postInPage]['postCount']))
                {
                    Listener::$threadPostsData[$this->thread_id][$postInThread['user_id']]['pages'][$postInPage]['postCount'] = 1;
                }
                else
                {
                    Listener::$threadPostsData[$this->thread_id][$postInThread['user_id']]['pages'][$postInPage]['postCount']++;
                }
                Listener::$threadPostsData[$this->thread_id][$postInThread['user_id']]['pages'][$postInPage]['posts'][$postInThread['post_id']] = [
                    'postId' => $postInThread['post_id'],
                    'signatureShown' => false
                ];
            }
        }

        if ($showSignatureOncePerThread)
        {
            $firstPageNumberWithPost = key(Listener::$threadPostsData[$this->thread_id][$this->user_id]['pages']);
            $firstPostIdInThreadByUser = key(Listener::$threadPostsData[$this->thread_id][$this->user_id]['pages'][$firstPageNumberWithPost]['posts']);
            Listener::$threadPostsData[$this->thread_id][$this->user_id]['pages'][$firstPageNumberWithPost]['posts'][$firstPostIdInThreadByUser]['signatureShown'] = true;
        }
        else
        {
            Listener::$threadPostsData[$this->thread_id][$this->user_id]['pages'][$currentPage]['posts'][key(Listener::$threadPostsData[$this->thread_id][$this->user_id]['pages'][$currentPage]['posts'])]['signatureShown'] = true;
        }

        return Listener::$threadPostsData[$this->thread_id][$this->user_id]['pages'][$currentPage]['posts'][$this->post_id]['signatureShown'];
    }
}