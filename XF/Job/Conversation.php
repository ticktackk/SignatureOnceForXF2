<?php

namespace TickTackk\SignatureOnce\XF\Job;

use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;

/**
 * @since 2.0.0 Alpha 1
 */
class Conversation extends XFCP_Conversation
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
        $signatureOnceRepo->getHandler('conversation_message')->rebuildContainerFirstUserContentRecords(
            'conversation',
            $id
        );
    }
}