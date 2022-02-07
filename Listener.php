<?php

namespace TickTackk\SignatureOnce;

use XF\Service\User\DeleteCleanUp as UserDeleteCleanUpSvc;
use XF\Service\User\ContentChange as UserContentChangeSvc;

/**
 * @since 2.0.0
 */
class Listener
{
    /**
     * @param UserDeleteCleanUpSvc $deleteService The service being initialized.
     * @param array $deletes A list of tables and where clauses representing content to be removed when a user is deleted.
     *
     * @return void
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public static function userDeleteCleanInit(
        UserDeleteCleanUpSvc $deleteService,
        array &$deletes
    ) : void
    {
        $deletes['xf_tck_signature_once_container_first_user_content'] = 'user_id = ?';
    }

    /**
     * Register the updates that need to happen when a user is renamed, deleted, etc.
     *
     * @param UserContentChangeSvc $changeService The service being initialized.
     * @param array $updates A list of tables and columns within that need to be updated when this service runs.
     *
     * @return void
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public static function userContentChangeInit(
        UserContentChangeSvc $changeService,
        array &$updates
    ) : void
    {
        // this is combined with queries in \TickTackk\SignatureOnce\XF\Service\User\ContentChange::stepMergeContainerFirstUserContentForTckSignatureOnce() to do a "merge"
        $updates['xf_tck_signature_once_container_first_user_content'] = ['user_id'];
    }
}