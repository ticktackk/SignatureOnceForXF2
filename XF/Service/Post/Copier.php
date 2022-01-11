<?php

namespace TickTackk\SignatureOnce\XF\Service\Post;

use TickTackk\SignatureOnce\Repository\Container as ContainerRepo;

/**
 * @since 2.0.0 Alpha 1
 */
class Copier extends XFCP_Copier
{
    /**
     * @return void
     *
     * @throws \XF\Db\Exception
     */
    protected function updateTargetData()
    {
        parent::updateTargetData();

        /** @var ContainerRepo $containerRepo */
        $containerRepo = $this->repository('TickTackk\SignatureOnce:Container');
        $containerRepo->rebuildContainerFirstUserContentRecords($this->getTarget());
    }
}