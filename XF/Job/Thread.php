<?php

namespace TickTackk\SignatureOnce\XF\Job;

use TickTackk\SignatureOnce\Repository\Container as ContainerRepo;
use TickTackk\SignatureOnce\XF\Entity\Thread as ExtendedThreadEntity;

/**
 * @since 2.0.0 Alpha 1
 */
class Thread extends XFCP_Thread
{
    /**
     * @param int $id
     *
     * @return void
     *
     * @throws \XF\Db\Exception
     */
    protected function rebuildById($id)
    {
        parent::rebuildById($id);

        /** @var ExtendedThreadEntity $thread */
        $thread = $this->app->em()->find('XF:Thread', $id);
        if ($thread)
        {
            /** @var ContainerRepo $containerRepo */
            $containerRepo = $this->app->repository('TickTackk\SignatureOnce:Container');
            $containerRepo->rebuildContainerFirstUserContentRecords($thread);
        }
    }
}