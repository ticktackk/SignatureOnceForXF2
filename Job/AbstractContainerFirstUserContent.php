<?php

namespace TickTackk\SignatureOnce\Job;

use TickTackk\SignatureOnce\Repository\SignatureOnce as SignatureOnceRepo;
use XF\Job\AbstractRebuildJob;
use XF\App as BaseApp;
use XF\Mvc\Entity\Repository;
use XF\Phrase;
use XF\Db\AbstractAdapter as DbAdapter;

/**
 * @since 2.0.0
 */
abstract class AbstractContainerFirstUserContent extends AbstractRebuildJob
{
    abstract protected function getContainerType() : string;

    abstract protected function getContentType() : string;

    /**
     * @param int $id
     *
     * @return void
     *
     * @throws \XF\Db\Exception
     * @throws \Exception
     */
    protected function rebuildById($id) : void
    {
        $handler = $this->getSignatureOnceRepo()->getHandler($this->getContentType());
        if (!$handler)
        {
            return;
        }

        $handler->rebuildContainerFirstUserContentRecords($this->getContainerType(), $id);
    }

    /**
     * @return Phrase
     */
    protected function getStatusType() : Phrase
    {
        return \XF::phrase($this->app()->getContentTypePhraseName($this->getContainerType(), true));
    }

    /**
     * @return Repository|SignatureOnceRepo
     */
    protected function getSignatureOnceRepo() : SignatureOnceRepo
    {
        return $this->repository('TickTackk\SignatureOnce:SignatureOnce');
    }

    protected function app() : BaseApp
    {
        return $this->app;
    }

    protected function db() : DbAdapter
    {
        return $this->app()->db();
    }

    protected function repository(string $identifier) : Repository
    {
        return $this->app()->repository($identifier);
    }
}