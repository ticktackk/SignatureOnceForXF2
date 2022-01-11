<?php

namespace TickTackk\SignatureOnce\XF\Job;

use TickTackk\SignatureOnce\Repository\Container as ContainerRepo;
use TickTackk\SignatureOnce\XF\Entity\ConversationMaster as ExtendedConversationMasterEntity;

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
     * @throws \XF\Db\Exception
     */
    protected function rebuildById($id)
    {
        parent::rebuildById($id);

        /** @var ExtendedConversationMasterEntity $conversation */
        $conversation = $this->app->em()->find('XF:ConversationMaster', $id);
        if ($conversation)
        {
            /** @var ContainerRepo $containerRepo */
            $containerRepo = $this->app->repository('TickTackk\SignatureOnce:Container');
            $containerRepo->rebuildContainerFirstUserContentRecords($conversation);
        }
    }
}