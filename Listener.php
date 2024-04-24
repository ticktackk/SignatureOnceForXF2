<?php

namespace TickTackk\SignatureOnce;

use TickTackk\SignatureOnce\Entity\ContentTrait as EntityContentTrait;
use TickTackk\SignatureOnce\XF\Entity\ConversationMessage as ExtendedConversationMessageEntity;
use TickTackk\SignatureOnce\XF\Entity\Post as ExtendedPostEntity;
use TickTackk\SignatureOnce\XF\Entity\UserOption as ExtendedUserOptionEntity;
use XF\App as BaseApp;
use XF\Entity\User as UserEntity;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Manager as EntityManager;
use XF\Service\User\DeleteCleanUp as UserDeleteCleanUpSvc;
use XF\Service\User\ContentChange as UserContentChangeSvc;

/**
 * @since 2.0.0
 * @version 2.0.7
 */
class Listener
{
    /**
     * @since 2.0.7
     *
     * @var array|\class-string[][]
     */
    protected static array $templateMacroData = [
        'public:post_macros:post' => [
            'post' => [
                ExtendedPostEntity::class => 'User'
            ]
        ],
        'public:conversation_message_macros:message' => [
            'message' => [
                ExtendedConversationMessageEntity::class => 'User'
            ]
        ]
    ];

    /**
     * @since 2.0.7
     *
     * @var int[]
     */
    protected static array $showSigUpdatedForUserIds = [];

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
     * @version 2.0.2
     *
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

    /**
     * @since 2.0.6
     * @version 2.0.7
     *
     * Allows the modification of various properties for template macros before they are rendered.
     *
     * Event hint: A string representing the template type, template name and macro name, e.g. public:template_name:macro_name.
     *
     * @param \XF\Template\Templater $templater Templater object.
     * @param mixed $type Template type.
     * @param mixed $template Template name.
     * @param mixed $name Macro name.
     * @param array $arguments Array of arguments passed to this macro.
     * @param array $globalVars Array of global vars available to this macro.
     *
     * @noinspection PhpUnusedParameterInspection
     *
     * @throws \Exception
     */
    public static function templaterMacroPreRender(
        \XF\Template\Templater $templater,
        &$type,
        &$template,
        &$name,
        array &$arguments,
        array &$globalVars
    ): void
    {
        /** @var EntityContentTrait $content */
        $content = null;
        $user = null;

        $fullName = "$type:$template:$name";
        $templateMacroData = static::$templateMacroData[$fullName] ?? null;
        if (is_array($templateMacroData))
        {
            foreach ($templateMacroData AS $contentKey => $classAndUserRelation)
            {
                if (!isset($arguments[$contentKey]))
                {
                    continue;
                }

                $contentClass = array_key_first($classAndUserRelation);
                $userRelation = $classAndUserRelation[$contentClass];

                /** @var Entity $content */
                $content = $arguments[$contentKey];
                if ((!$content instanceof $contentClass) || (!$content->hasRelation($userRelation)))
                {
                    $content = null;
                    $user = null;
                }

                $user = $content->getRelation($userRelation);
            }
        }

        if (($content !== null) && ($user instanceof UserEntity) && (method_exists($content, 'canShowSignature')))
        {
            if (!$user->Option->hasOption('tck_show_signature'))
            {
                return;
            }

            $user->Option->setOption('tck_show_signature', $content->canShowSignature());
            static::$showSigUpdatedForUserIds[$user->user_id] = $user->user_id;
        }
    }

    /**
     * @since 2.0.7
     *
     * Allows the modification of the rendered macro output.
     *
     * Event hint: A string representing the template type, template name and macro name, e.g. public:template_name:macro_name.
     *
     * @param \XF\Template\Templater $templater Templater object.
     * @param mixed $type Template type.
     * @param mixed $template Template name.
     * @param mixed $name Macro name.
     * @param mixed $output Rendered output.
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public static function templaterMacroPostRender(
        \XF\Template\Templater $templater,
        $type,
        $template,
        $name,
        &$output
    ): void
    {
        if (empty(static::$showSigUpdatedForUserIds))
        {
            return;
        }

        foreach (array_keys(static::$showSigUpdatedForUserIds) AS $userId)
        {
            /** @var ExtendedUserOptionEntity $userOption */
            $userOption = static::em()->findCached('XF:UserOption', $userId);
            if (!$userOption)
            {
                continue;
            }

            if (!$userOption->hasOption('tck_show_signature'))
            {
                continue;
            }

            $userOption->setOption('tck_show_signature', null);
        }
    }

    protected static function app() : BaseApp
    {
        return \XF::app();
    }

    protected static function em() : EntityManager
    {
        return static::app()->em();
    }
}