<?php

namespace TickTackk\SignatureOnce\XF\Service\Post;

use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;

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

        /** @var SignatureOnceRepo $signatureOnceRepo */
        $signatureOnceRepo = $this->repository('TickTackk\SignatureOnce:SignatureOnce');
        $signatureOnceRepo->getHandler('post')->rebuildContainerFirstUserContentRecords(
            'thread',
            $this->getTarget()->getEntityId()
        );
    }
}