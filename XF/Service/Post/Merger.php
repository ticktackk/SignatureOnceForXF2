<?php

namespace TickTackk\SignatureOnce\XF\Service\Post;

use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;

/**
 * @since 2.0.0 Alpha 1
 */
class Merger extends XFCP_Merger
{
    /**
     * @return void
     *
     * @throws \Exception
     */
    protected function updateSourceData()
    {
        parent::updateSourceData();

        /** @var SignatureOnceRepo $signatureOnceRepo */
        $signatureOnceRepo = $this->repository('TickTackk\SignatureOnce:SignatureOnce');

        foreach ($this->sourceThreads AS $sourceThread)
        {
            if (!array_key_exists($sourceThread->first_post_id, $this->sourcePosts) || $sourceThread->reply_count)
            {
                $signatureOnceRepo->getHandler('post')->rebuildContainerFirstUserContentRecords(
                    'thread',
                    $sourceThread->getEntityId()
                );
            }
        }
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    protected function cleanupActions()
    {
        parent::cleanupActions();

        $target = $this->getTarget();
        $targetThread = $target->Thread;

        /** @var SignatureOnceRepo $signatureOnceRepo */
        $signatureOnceRepo = $this->repository('TickTackk\SignatureOnce:SignatureOnce');
        $signatureOnceRepo->getHandler('post')->rebuildContainerFirstUserContentRecords(
            'thread',
            $targetThread->getEntityId()
        );
    }
}