<?php

namespace TickTackk\SignatureOnce\Job;

/**
 * @since 2.0.0 Alpha 1
 */
class ConversationFirstUserMessage extends AbstractContainerFirstUserContent
{
    protected function getContainerType(): string
    {
        return 'conversation';
    }

    protected function getContentType(): string
    {
        return 'conversation_message';
    }

    protected function getNextIds($start, $batch) : array
    {
        $db = $this->db();

        return $db->fetchAllColumn($db->limit(
            "
				SELECT conversation_id
				FROM xf_conversation_master
				WHERE conversation_id > ?
				ORDER BY conversation_id
			", $batch
        ), $start);
    }
}