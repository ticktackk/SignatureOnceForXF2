<?php

namespace TickTackk\SignatureOnce\Entity;

use XF\Mvc\Entity\Structure as EntityStructure;
use XF\Phrase;

/**
 * @version 2.0.0
 */
trait ContentTrait
{
    /**
     * @version 2.0.0
     *
     * @param Phrase|null $error
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function canShowSignature(?Phrase &$error = null) : bool
    {
        return $this->getHandlerForTckSignatureOnce($this->getEntityContentType())->canShowSignature(
            $this,
            $error
        );
    }

    /**
     * @since 2.0.0
     *
     * @param EntityStructure $structure
     * @param string $relationName
     * @param string $containerType
     * @param string $containerIdKey
     * @param string $userIdKey
     *
     * @return void
     */
    protected static function setupContainerEntityStructureForTckSignatureOnce(
        EntityStructure $structure,
        string $relationName,
        string $containerType,
        string $containerIdKey,
        string $userIdKey = 'user_id'
    ) : void
    {
        $structure->relations[$relationName] = [
            'entity' => 'TickTackk\SignatureOnce:ContainerFirstUserContent',
            'type' => static::TO_ONE,
            'conditions' => [
                ['user_id', '=', '$' . $userIdKey],
                ['container_type', '=', $containerType],
                ['container_id', '=', '$' . $containerIdKey],
                ['content_type', '=', $structure->contentType]
            ]
        ];

        if (array_key_exists('full', $structure->withAliases))
        {
            $structure->withAliases['full'][] = $relationName;
        }
        else
        {
            $structure->defaultWith[] = $relationName;
        }
    }
}