<?php

namespace TickTackk\SignatureOnce\XF\Job;

use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;

/**
 * @since 2.0.0
 */
class Thread extends XFCP_Thread
{
    /**
     * @param int $id
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function rebuildById($id)
    {
        parent::rebuildById($id);

        /** @var SignatureOnceRepo $signatureOnceRepo */
        $signatureOnceRepo = $this->app->repository('TickTackk\SignatureOnce:SignatureOnce');
        $signatureOnceRepo->getHandler('post')->rebuildContainerFirstUserContentRecords(
            'thread',
            $id
        );
    }
}