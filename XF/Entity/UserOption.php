<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use XF\Mvc\Entity\Structure as EntityStructure;

/**
 * @since 2.0.6
 *
 * COLUMNS
 * @property bool $content_show_signature_
 *
 * GETTERS
 * @property bool $content_show_signature
 */
class UserOption extends XFCP_UserOption
{
    public function getContentShowSignature() : bool
    {
        try
        {
            return $this->getOption('tck_show_signature') ?? $this->content_show_signature_;
        }
        finally
        {
            $this->setOption('tck_show_signature', null);
        }
    }

    public static function getStructure(EntityStructure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['content_show_signature'] = false;
        $structure->options['tck_show_signature'] = null;

        return $structure;
    }
}