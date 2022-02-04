<?php

namespace TickTackk\SignatureOnce\XF\Service\Thread;

use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;

/**
 * @since 2.0.0
 */
class Merger extends XFCP_Merger
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
}