<?php

namespace TickTackk\SignatureOnce\XF\Service\Post;

use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;

/**
 * @since 2.0.0 Alpha 1
 */
class Mover extends XFCP_Mover
{
    /**
     * @return void
     *
     * @throws \Exception
     */
    protected function updateTargetData()
    {
        parent::updateTargetData();

        /** @var SignatureOnceRepo $signatureOnceRepo */
        $signatureOnceRepo = $this->repository('TickTackk\SignatureOnce:SignatureOnce');
        $signatureOnceRepo->getHandler('post')->rebuildContainerFirstUserContentRecords(
            'thread',
            $this->getTarget()->getEntityId()
        );
    }

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
}