<?php

namespace TickTackk\SignatureOnce\Cli\Command\Rebuild;

use XF\Cli\Command\Rebuild\AbstractRebuildCommand;

/**
 * @since 2.0.0 Alpha 1
 */
class RebuildThreadFirstUserPost extends AbstractRebuildCommand
{
    /**
     * @inheritDoc
     */
    protected function getRebuildName() : string
    {
        return 'tck-signature-once-thread-first-user-post-records';
    }

    /**
     * @inheritDoc
     */
    protected function getRebuildDescription() : string
    {
        return 'Rebuilds thread first user post records.';
    }

    /**
     * @inheritDoc
     */
    protected function getRebuildClass() : string
    {
        return 'TickTackk\SignatureOnce:ThreadFirstUserPost';
    }
}