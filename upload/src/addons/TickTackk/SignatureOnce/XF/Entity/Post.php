<?php

namespace TickTackk\SignatureOnce\XF\Entity;

/**
 * Class Post
 *
 * @package TickTackk\SignatureOnce
 */
class Post extends XFCP_Post
{
    /**
     * @return bool
     */
    public function canShowSignature()
    {
        if ($this->canBypassSignatureOnce())
        {
            return true;
        }

        /** @var \TickTackk\SignatureOnce\XF\Repository\Post $postRepo */
        $postRepo = $this->repository('XF:Post');
        list($currentPage, $showSignatureOncePerThread, $threadPostCache) = $postRepo->getCachedPostsForThreadForSignatureOnce($this->Thread, $this);

        if ($showSignatureOncePerThread)
        {
            $firstPageNumberWithPost = key($threadPostCache[$this->user_id]['pages']);
            $firstPostIdInThreadByUser = key($threadPostCache[$this->user_id]['pages'][$firstPageNumberWithPost]['posts']);
            $threadPostCache[$this->user_id]['pages'][$firstPageNumberWithPost]['posts'][$firstPostIdInThreadByUser]['signatureShown'] = true;
        }
        else
        {
            $threadPostCache[$this->user_id]['pages'][$currentPage]['posts'][key($threadPostCache[$this->user_id]['pages'][$currentPage]['posts'])]['signatureShown'] = true;
        }

        $postRepo->updateCachedPostsForThreadForSignatureOnce($this->Thread, $threadPostCache);
        return $threadPostCache[$this->user_id]['pages'][$currentPage]['posts'][$this->post_id]['signatureShown'];
    }

    /**
     * @param null|string $error
     *
     * @return bool
     */
    public function canBypassSignatureOnce(/** @noinspection PhpUnusedParameterInspection */
        &$error = null)
    {
        $thread = $this->Thread;
        $visitor = \XF::visitor();

        $nodeId = $thread->node_id;

        return $visitor->hasNodePermission($nodeId, 'bypassSignatureOnce');
    }
}