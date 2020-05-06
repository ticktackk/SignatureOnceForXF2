<?php

namespace TickTackk\SignatureOnce\ControllerPlugin;

use TickTackk\SignatureOnce\Entity\ContentTrait as ContentEntityTrait;
use XF\App as BaseApp;
use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Reply\View;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\AbstractReply;
use XF\Repository\ConversationMessage as ConversationMessageRepo;
use XF\Mvc\Entity\Repository;
use XF\Util\Php as PhpUtil;
use TickTackk\SignatureOnce\XF\Entity\Thread as ExtendedThreadEntity;
use TickTackk\SignatureOnce\XF\Entity\Post as ExtendedPostEntity;
use TickTackk\SignatureOnce\XF\Entity\ConversationMaster as ExtendedConversationMasterEntity;
use TickTackk\SignatureOnce\XF\Entity\ConversationMessage as ExtendedConversationMessageEntity;
use XF\Db\AbstractAdapter as DbAdapter;
use Doctrine\Common\Cache\CacheProvider as CacheProvider;

/**
 * Class SignatureOnce
 *
 * @package TickTackk\SignatureOnce\ControllerPlugin
 */
class SignatureOnce extends AbstractPlugin
{
    /**
     * @return bool
     */
    public function isSignatureShownOncePerPage()
    {
        return !$this->options()->showSignatureOncePerThread;
    }

    /**
     * @param AbstractReply $reply
     * @param string|Entity $containerKey
     * @param string|ArrayCollection|Entity $messagesKey
     * @param string|int|null $pageKey
     */
    public function setShowSignature(AbstractReply $reply, $containerKey, $messagesKey, $pageKey = 'page') : void
    {
        if (!$reply instanceof ViewReply)
        {
            return;
        }

        $container = $reply->getParam($containerKey);
        if (!$container instanceof Entity)
        {
            return;
        }

        $messages = $reply->getParam($messagesKey);
        if (!$messages instanceof ArrayCollection)
        {
            return;
        }

        if (\is_int($pageKey))
        {
            $page = $pageKey;
        }
        else if ($pageKey === null)
        {
            $page = $this->calculateCurrentPageFromContainer($container, $messages);
        }
        else
        {
            if (!\array_key_exists($pageKey, $reply->getParams()))
            {
                return;
            }

            $page = \max(1, $reply->getParam($pageKey));
        }

        if ($this->isSignatureShownOncePerPage())
        {
            $userIdsFound = [];

            /** @var Entity|ContentEntityTrait $message */
            foreach ($messages AS $message)
            {
                $userId = $message->getUserIdForTckSignatureOnce();
                $message->setShowSignature(!\in_array($userId, $userIdsFound, true));
                $userIdsFound[] = $userId;
            }
        }
        else
        {
            $completeContainerCounts = $this->getCompleteContainerCounts($container, $messages, $page);

            /** @var Entity|ContentEntityTrait $message */
            foreach ($messages AS $message)
            {
                $messageId = $message->getEntityId();
                if (\array_key_exists($messageId, $completeContainerCounts))
                {
                    $message->setShowSignature($completeContainerCounts[$messageId] === null);
                }
            }
        }
    }

    /**
     * @param Entity $container
     * @param ArrayCollection|Entity[]|ContentEntityTrait[] $messages
     *
     * @return int
     */
    protected function calculateCurrentPageFromContainer(Entity $container, $messages) : int
    {
        $methodName = 'calculateCurrentPageFrom' . PhpUtil::camelCase($container->getEntityContentType());
        if (!\method_exists($this, $methodName))
        {
            return 1;
        }

        return $this->{$methodName}($container, $messages);
    }

    /**
     * @param Entity $container
     * @param ArrayCollection|ExtendedPostEntity[] $messages
     *
     * @return int
     */
    protected function calculateCurrentPageFromThread(
        /** @noinspection PhpUnusedParameterInspection */Entity $container, ArrayCollection $messages
    ) : int
    {
        $perPage = $this->app()->options()->messagesPerPage;
        $lastPosition = \max($messages->pluckNamed('position'));

        return (int) \max(1, \ceil($lastPosition / $perPage));
    }

    /**
     * @param Entity|ExtendedConversationMasterEntity $container
     * @param ArrayCollection|ExtendedConversationMessageEntity[] $messages
     *
     * @return int
     */
    protected function calculateCurrentPageFromConversation(Entity $container, ArrayCollection $messages) : int
    {
        $perPage = $this->app()->options()->messagesPerPage;
        $lastDate = \max($messages->pluckNamed('message_date'));

        $conversationMessageRepo = $this->getConversationMessageRepo();
        $conversationMessageTotal = $conversationMessageRepo->findMessagesForConversationView($container)
            ->where('message_date', '<=', $lastDate)
            ->total();

        return \floor($conversationMessageTotal / $perPage) + 1;
    }

    /**
     * @param string $query
     *
     * @return string
     */
    protected function getCompleteContainerCountsQueryHash(string $query) : string
    {
        return \md5($query);
    }

    /**
     * @param string $query
     *
     * @return string
     */
    protected function getCompleteContainerCountsCacheKey(string $query) : string
    {
        return 'tckSignatureOnce_' . $this->getCompleteContainerCountsQueryHash($query);
    }

    /**
     * @param string $query
     *
     * @return array|null
     */
    protected function getCompleteContainerCountsFromCache(string $query) :? array
    {
        $cache = $this->cache();
        if (!$cache)
        {
            return null;
        }

        $cacheKey = $this->getCompleteContainerCountsCacheKey($query);
        if (!$cache->contains($cacheKey))
        {
            return null;
        }

        return $cache->fetch($this->getCompleteContainerCountsCacheKey($query));
    }

    /**
     * @param string $query
     * @param array $results
     * @param int $lifeTime
     */
    protected function cacheCompleteContainerCounts(string $query, array $results, int $lifeTime = 3600) : void
    {
        $cache = $this->cache();
        if (!$cache)
        {
            return;
        }

        $cache->save($this->getCompleteContainerCountsCacheKey($query), $results, $lifeTime);
    }

    /**
     * @param Entity $container
     * @param $messages
     * @param int $page
     *
     * @return array
     */
    protected function getCompleteContainerCounts(Entity $container, $messages, int $page)
    {
        $methodName = 'getComplete' . PhpUtil::camelCase($container->getEntityContentType()) . 'Counts';
        if (!\method_exists($this, $methodName))
        {
            return [];
        }

        $query = $this->{$methodName}($container, $messages, $page);
        if (!$query)
        {
            return [];
        }

        $fromCache = $this->getCompleteContainerCountsFromCache($query);
        if ($fromCache)
        {
            return $fromCache;
        }

        $results = $this->db()->fetchPairs($query);
        $this->cacheCompleteContainerCounts($query, $results);

        return $results;
    }

    /**
     * @param Entity|ExtendedThreadEntity $container
     * @param ArrayCollection|Entity|ExtendedPostEntity|ExtendedPostEntity[] $messages
     * @param int $page
     *
     * @return string
     */
    protected function getCompleteThreadCounts(Entity $container, $messages, int $page) : string
    {
        $perPage = $this->options()->messagesPerPage;
        $page = \max(1, $page);

        $start = ($page - 1) * $perPage;
        $end = $start + $perPage;

        $viewableStates = ['visible'];
        if ($container->canViewDeletedPosts())
        {
            $viewableStates[] = 'deleted';
        }
        if ($container->canViewModeratedPosts())
        {
            $viewableStates[] = 'moderated';
        }

        $db = $this->db();
        $viewableStatesStr = $db->quote($viewableStates);
        $containerId = $db->quote($container->thread_id);
        $startQuoted = $db->quote($start);
        $endQuoted = $db->quote($end);

        return "
            SELECT post_main.post_id,
                   (SELECT post_id 
                   FROM xf_post AS post_tmp
                   WHERE post_tmp.user_id = post_main.user_id
                       AND post_tmp.position < post_main.position
                       AND post_tmp.position < post_main.position
                       AND post_tmp.thread_id = {$containerId}
                       AND post_tmp.message_state IN ({$viewableStatesStr})
                   ORDER BY post_tmp.post_id ASC
                   LIMIT 1
                   ) AS previous_post_id
            FROM
            (
                SELECT DISTINCT user_id, post_id, `position`
                FROM xf_post AS post
                WHERE post.thread_id = {$containerId}
                  AND post.message_state IN ({$viewableStatesStr})
                  AND post.position >= {$startQuoted}
                  AND post.position < {$endQuoted}
            ) AS post_main";
    }

    /**
     * @param Entity|ExtendedConversationMasterEntity $container
     * @param ArrayCollection|ExtendedConversationMessageEntity[]|Entity|ExtendedConversationMessageEntity $messages
     * @param int $page
     *
     * @return string
     */
    protected function getCompleteConversationCounts(Entity $container, $messages, int $page) : string
    {
        $db = $this->db();

        /**
         * @var ExtendedConversationMessageEntity $firstMessage
         * @var ExtendedConversationMessageEntity $lastMessage
         */
        $firstMessage = $messages instanceof ArrayCollection ? $messages->first() : $container->FirstMessage;
        $lastMessage = $messages instanceof ArrayCollection ? $messages->last() : $container->LastMessage;

        $containerId = $db->quote($container->conversation_id);
        $startQuoted = $db->quote($firstMessage->message_id);
        $endQuoted = $db->quote($lastMessage->message_id);

        return "
            SELECT
                conversation_message_main.message_id,
                (
                  SELECT message_id 
                  FROM xf_conversation_message AS conversation_message_tmp
                  WHERE conversation_message_tmp.user_id = conversation_message_main.user_id
                    AND conversation_message_tmp.message_id < conversation_message_main.message_id
                    AND conversation_message_tmp.conversation_id = {$containerId}
                  ORDER BY conversation_message_tmp.message_id ASC
                  LIMIT 1
                ) AS previous_message_id
            FROM
            (
                SELECT DISTINCT user_id, message_id
                FROM xf_conversation_message AS conversation_message
                WHERE conversation_message.conversation_id = {$containerId}
                  AND conversation_message.message_id >= {$startQuoted}
                  AND conversation_message.message_id <= {$endQuoted}
            ) AS conversation_message_main";
    }

    /**
     * @return BaseApp
     */
    protected function app() : BaseApp
    {
        return $this->app;
    }

    /**
     * @return CacheProvider|null
     */
    protected function cache() :? CacheProvider
    {
        return $this->app()->cache();
    }

    /**
     * @return DbAdapter
     */
    protected function db() : DbAdapter
    {
        return $this->app()->db();
    }

    /**
     * @return Repository|ConversationMessageRepo
     */
    protected function getConversationMessageRepo() : ConversationMessageRepo
    {
        return $this->repository('XF:ConversationMessage');
    }
}