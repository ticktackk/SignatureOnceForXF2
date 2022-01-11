<?php

namespace TickTackk\SignatureOnce\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure as EntityStructure;
use XF\Entity\User as UserEntity;

/**
 * COLUMNS
 * @property int|null record_id
 * @property int $user_id
 * @property string $container_type
 * @property int $container_id
 * @property string $content_type
 * @property string $content_id
 * @property int $content_date
 *
 * RELATIONS
 * @property UserEntity $User
 *
 * GETTERS
 * @property Entity $Container
 * @property Entity $Content
 */
class ContainerFirstUserContent extends Entity
{
    public function getContainer() :? Entity
    {
        return $this->app()->findByContentType($this->container_type, $this->container_id);
    }

    public function getContent() :? Entity
    {
        return $this->app()->findByContentType($this->content_type, $this->content_id);
    }

    public static function getStructure(EntityStructure $structure) : EntityStructure
    {
        $structure->shortName = 'TickTackk\SignatureOnce:ContainerFirstUserContent';
        $structure->table = 'xf_tck_signature_once_container_first_user_content';
        $structure->primaryKey = 'record_id';
        $structure->columns = [
            'record_id' => ['type' => static::UINT, 'autoIncrement' => true, 'nullable' => true],
            'user_id' => ['type' => static::UINT, 'required' => true],
            'container_type' => ['type' => static::STR, 'maxLength' => 25, 'required' => true],
            'container_id' => ['type' => static::UINT, 'required' => true],
            'content_type' => ['type' => static::STR, 'maxLength' => 25, 'required' => true],
            'content_id' => ['type' => static::UINT, 'required' => true],
            'content_date' => ['type' => static::UINT, 'required' => true]
        ];
        $structure->relations = [
            'User' => [
                'entity' => 'XF:User',
                'type' => static::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ]
        ];
        $structure->getters = [
            'Container' => true,
            'Content' => true
        ];

        return $structure;
    }
}