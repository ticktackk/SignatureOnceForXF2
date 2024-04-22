<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use XF\Mvc\Entity\Structure as EntityStructure;

/**
 * @since 2.0.6
 * @version 2.0.7
 *
 * COLUMNS
 * @property bool $content_show_signature_
 *
 * GETTERS
 * @property bool $content_show_signature
 */
class UserOption extends XFCP_UserOption
{
    /**
     * @version 2.0.7
     *
     * @return bool
     */
    public function getContentShowSignature() : bool
    {
        return $this->getOption('tck_show_signature') ?? $this->content_show_signature_;
    }

    public static function getStructure(EntityStructure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['content_show_signature'] = false;
        $structure->options['tck_show_signature'] = null;

        return $structure;
    }
}