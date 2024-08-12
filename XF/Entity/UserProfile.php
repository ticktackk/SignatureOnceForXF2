<?php

namespace TickTackk\SignatureOnce\XF\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure as EntityStructure;

/**
 * @since 2.0.8
 *
 * COLUMNS
 * @property string $signature_
 */
class UserProfile extends XFCP_UserProfile
{
    /**
     * @return string
     */
    public function getSignature()
    {
        if ($this->getOption('tck_show_signature') === false)
        {
            return '';
        }

        return $this->signature_;
    }

    /**
     * @param EntityStructure $structure
     *
     * @return EntityStructure
     */
    public static function getStructure(EntityStructure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['signature'] = false;
        $structure->options['tck_show_signature'] = null;
    
        return $structure;
    }
}