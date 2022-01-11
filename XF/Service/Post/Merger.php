<?php

namespace TickTackk\SignatureOnce\XF\Service\Post;

use TickTackk\SignatureOnce\Repository\Container as ContainerRepo;

/**
 * @since 2.0.0 Alpha 1
 */
class Merger extends XFCP_Merger
{
    /**
     * @return void
     *
     * @throws \XF\Db\Exception
     */
    protected function updateSourceData()
    {
        parent::updateSourceData();

        /** @var ContainerRepo $containerRepo */
        $containerRepo = $this->repository('TickTackk\SignatureOnce:Container');

        foreach ($this->sourceThreads AS $sourceThread)
        {
            if (!array_key_exists($sourceThread->first_post_id, $this->sourcePosts) || $sourceThread->reply_count)
            {
                $containerRepo->rebuildContainerFirstUserContentRecords($sourceThread);
            }
        }
    }

    /**
     * @return void
     *
     * @throws \XF\Db\Exception
     */
    protected function cleanupActions()
    {
        parent::cleanupActions();

        $target = $this->getTarget();
        $targetThread = $target->Thread;

        /** @var ContainerRepo $containerRepo */
        $containerRepo = $this->repository('TickTackk\SignatureOnce:Container');
        $containerRepo->rebuildContainerFirstUserContentRecords($targetThread);
    }
}