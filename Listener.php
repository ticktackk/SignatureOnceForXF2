<?php

namespace TickTackk\SignatureOnce;

use TickTackk\SignatureOnce\Entity\ContentTrait as EntityContentTrait;
use TickTackk\SignatureOnce\XF\Entity\Post as ExtendedPostEntity;
use XF\Entity\User as UserEntity;
use TickTackk\SignatureOnce\XF\Entity\UserOption as ExtendedUserOptionEntity;
use XF\Service\User\DeleteCleanUp as UserDeleteCleanUpSvc;
use XF\Service\User\ContentChange as UserContentChangeSvc;

/**
 * @since 2.0.0
 * @version 2.0.6
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

        switch ("$type:$template:$name")
        {
            case 'public:post_macros:post':
                $content = $arguments['post'] ?? null;
                if ($content instanceof ExtendedPostEntity)
                {
                    $user = $content->User;
                }
                break;

            case 'public:conversation_message_macros:message':
                $content = $arguments['message'] ?? null;
                if ($content instanceof ExtendedPostEntity)
                {
                    $user = $content->User;
                }
                break;
        }

        if (($content !== null) && ($user instanceof UserEntity) && (method_exists($content, 'canShowSignature')))
        {
            if (!$user->Option->hasOption('tck_show_signature'))
            {
                return;
            }

            $user->Option->setOption('tck_show_signature', $content->canShowSignature());
        }
    }
}