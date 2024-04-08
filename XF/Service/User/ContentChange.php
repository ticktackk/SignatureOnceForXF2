<?php

namespace TickTackk\SignatureOnce\XF\Service\User;

use XF\App as BaseApp;

/**
 * @since 2.0.0
 */
class ContentChange extends XFCP_ContentChange
{
    public function __construct(
        BaseApp $app,
        $originalUserId,
        $originalUsername = null
    )
    {
        parent::__construct(
            $app,
            $originalUserId,
            $originalUsername
        );

        $this->steps[] = 'stepMergeContainerFirstUserContentForTckSignatureOnce';
    }

    /**
     * @param mixed $stepLastOffset
     * @param int|float $remainingTime
     *
     * @return void
     *
     * @throws \XF\Db\Exception
     *
     * @noinspection PhpUnusedParameterInspection
     */
    protected function stepMergeContainerFirstUserContentForTckSignatureOnce(
        $stepLastOffset,
        $remainingTime
    ) : void
    {
        $originalUserId = $this->getOriginalUserId();
        $newUserId = $this->getNewUserId();
        if ($newUserId === null)
        {
            return;
        }

        // merge the container first user content records here for accurate values
        $db = $this->db();
        $db->beginTransaction();

        $db->query("
			UPDATE xf_tck_signature_once_container_first_user_content AS source,
			    xf_tck_signature_once_container_first_user_content AS target
			SET target.content_date = IF(target.content_date > source.content_date, target.content_date, source.content_date),
			    target.content_id = IF(target.content_date > source.content_date, target.content_id, source.content_id)
			WHERE source.user_id = ?
				AND source.container_type = target.container_type
			    AND source.container_id = target.container_id
			    AND source.content_type = target.content_type
				AND target.user_id = ?
		", [$originalUserId, $newUserId]);
        $db->delete('xf_tck_signature_once_container_first_user_content', 'user_id = ?', $originalUserId);

        $db->commit();
    }
}