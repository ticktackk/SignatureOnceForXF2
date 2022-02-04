<?php

namespace TickTackk\SignatureOnce\Job;

/**
 * @since 2.0.0
 */
class ThreadFirstUserPost extends AbstractContainerFirstUserContent
{
    protected function getContainerType(): string
    {
        return 'thread';
    }

    protected function getContentType(): string
    {
        return 'post';
    }

    protected function getNextIds($start, $batch) : array
    {
        $db = $this->db();

        return $db->fetchAllColumn($db->limit(
            "
				SELECT thread_id
				FROM xf_thread
				WHERE thread_id > ?
				ORDER BY thread_id
			", $batch
        ), $start);
    }
}