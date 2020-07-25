<?php

namespace TickTackk\SignatureOnce\ControllerPlugin;

use TickTackk\SignatureOnce\Entity\ContainerInterface as ContainerEntityInterface;
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
use XF\Repository\Post as PostRepo;

/**
 * Class SignatureOnce
 *
 * @package TickTackk\SignatureOnce\ControllerPlugin
 */
class SignatureOnce extends AbstractPlugin
{
    /**
     * @param ContainerEntityInterface|Entity $container
     */
    public function isSignatureShownOncePerPage(ContainerEntityInterface $container) :? bool
    {
        switch ($container->getEntityContentType())
        {
            case 'thread':
                return !$this->options()->showSignatureOncePerThread;

            case 'conversation':
                return !$this->options()->showSignatureOncePerConversation;

            default:
                return null;
        }
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
        if ($messages instanceof ArrayCollection)
        {
            $messages = $messages->toArray();
        }
        else if ($messages instanceof Entity)
        {
            $messages = [$messages->getEntityId() => $messages];
        }
        else if (!\is_array($messages))
        {
            return;
        }

        $perPage = $this->getPerPageForContainer($container);
        if (\is_int($pageKey))
        {
            $page = $pageKey;
        }
        else if ($pageKey === null)
        {
            $page = $this->calculateCurrentPageFromContainer($container, $messages, $perPage);
        }
        else
        {
            if (!\array_key_exists($pageKey, $reply->getParams()))
            {
                return;
            }

            $page = \max(1, $reply->getParam($pageKey));
        }

        $isSignatureShownOncePerPage = $this->isSignatureShownOncePerPage($container);
        if ($isSignatureShownOncePerPage === null)
        {
            return;
        }

        if ($pageKey === null && \count($messages) !== $perPage) // from quick reply so force loading all messages
        {
            $messages = $this->getAllMessagesInCurrentPageForContainer($container, $page, $perPage, $messages);
        }

        $containerCounts = $this->getContainerCounts($container, $messages, $page);

        /** @var Entity|ContentEntityTrait $message */
        foreach ($messages AS $message)
        {
            $messageId = $message->getEntityId();
            if (\array_key_exists($messageId, $containerCounts))
            {
                $message->setShowSignature(
                    $containerCounts[$messageId] === $messageId || $containerCounts[$messageId] === null
                );
            }
        }
    }

    /**
     * @param ContainerEntityInterface|Entity $container
     * @return int|null
     */
    protected function getPerPageForContainer(ContainerEntityInterface $container) :? int
    {
        switch ($container->getEntityContentType())
        {
            case 'thread':
            case 'conversation':
                return $this->options()->messagesPerPage;

            default:
                return null;
        }
    }

    /**
     * @param ContainerEntityInterface|Entity $container
     */
    protected function getAllMessagesInCurrentPageForContainer(ContainerEntityInterface $container, int $currentPage, int $perPage, array $existingMessages)
    {
        $methodName = 'getAllMessagesInCurrentPageFor' . PhpUtil::camelCase($container->getEntityContentType());
        if (!\method_exists($this, $methodName) || \count($existingMessages) === $perPage)
        {
            return $existingMessages;
        }

        return $this->{$methodName}($container, $currentPage, $perPage);
    }

    /**
     * @param ContainerEntityInterface|Entity|ExtendedThreadEntity $container
     */
    protected function getAllMessagesInCurrentPageForThread(ContainerEntityInterface $container, int $currentPage, int $perPage) : array
    {
        return $this->getPostRepo()->findPostsForThreadView($container)
            ->onPage($currentPage, $perPage)
            ->fetch()
            ->toArray();
    }

    /**
     * @param ContainerEntityInterface|Entity|ExtendedConversationMasterEntity $container
     */
    protected function getAllMessagesInCurrentPageForConversation(ContainerEntityInterface $container, int $currentPage, int $perPage) : array
    {
        return $this->getConversationMessageRepo()->findMessagesForConversationView($container)
            ->limitByPage($currentPage, $perPage)
            ->fetch()
            ->toArray();
    }

    /**
     * @param ContainerEntityInterface|Entity $container
     * @param ArrayCollection|array|Entity[]|ContentEntityTrait[] $messages
     *
     * @return int
     */
    protected function calculateCurrentPageFromContainer(ContainerEntityInterface $container, array $messages, int $perPage) : int
    {
        $methodName = 'calculateCurrentPageFrom' . PhpUtil::camelCase($container->getEntityContentType());
        if (!\method_exists($this, $methodName))
        {
            return 1;
        }

        return $this->{$methodName}($container, $messages, $perPage);
    }

    /**
     * @param ContainerEntityInterface|Entity $container
     * @param ArrayCollection|array|ExtendedPostEntity[] $messages
     *
     * @return int
     */
    protected function calculateCurrentPageFromThread(/** @noinspection PhpUnusedParameterInspection */ContainerEntityInterface $container, array $messages, int $perPage) : int
    {
        $lastPosition = \max(\array_column($messages, 'position'));

        return (int) \max(1, \ceil($lastPosition / $perPage));
    }

    /**
     * @param ContainerEntityInterface|Entity|ExtendedConversationMasterEntity $container
     * @param ArrayCollection|array|ExtendedConversationMessageEntity[] $messages
     *
     * @return int
     */
    protected function calculateCurrentPageFromConversation(ContainerEntityInterface $container, array $messages, int $perPage) : int
    {
        $lastDate = \max(\array_column($messages, 'message_date'));

        $conversationMessageRepo = $this->getConversationMessageRepo();
        $conversationMessageTotal = $conversationMessageRepo->findMessagesForConversationView($container)
            ->where('message_date', '<', $lastDate)
            ->total();

        return \floor($conversationMessageTotal / $perPage) + 1;
    }

    /**
     * @param ContainerEntityInterface|Entity $container
     * @param string $query
     *
     * @return string
     */
    protected function getContainerCountsQueryHash(ContainerEntityInterface $container, string $query) : string
    {
        return \md5($query . "\n" . $container->getLastModifiedTimestampForSignatureOnce());
    }

    /**
     * @param ContainerEntityInterface|Entity $container
     * @param string $query
     *
     * @return string
     */
    protected function getContainerCountsCacheKey(ContainerEntityInterface $container, string $query) : string
    {
        return 'tckSignatureOnce_' . $this->getContainerCountsQueryHash($container, $query);
    }

    /**
     * @param ContainerEntityInterface|Entity $container
     * @param string $query
     *
     * @return array|null
     */
    protected function getContainerCountsFromCache(ContainerEntityInterface $container, string $query) :? array
    {
        $cache = $this->cache();
        if (!$cache)
        {
            return null;
        }

        $cacheKey = $this->getContainerCountsCacheKey($container, $query);
        if (!$cache->contains($cacheKey))
        {
            return null;
        }

        return $cache->fetch($cacheKey);
    }

    /**
     * @param ContainerEntityInterface $container
     * @param string $query
     * @param array $results
     * @param int $lifeTime
     */
    protected function cacheContainerCounts(ContainerEntityInterface $container, string $query, array $results, int $lifeTime = 3600) : void
    {
        $cache = $this->cache();
        if (!$cache)
        {
            return;
        }

        $cache->save($this->getContainerCountsCacheKey($container, $query), $results, $lifeTime);
    }

    /**
     * @param ContainerEntityInterface|Entity $container
     * @param ArrayCollection|array|ContentEntityTrait[] $messages
     * @param int $page
     *
     * @return array
     */
    protected function getContainerCounts(ContainerEntityInterface $container, $messages, int $page)
    {
        $methodName = 'get' . PhpUtil::camelCase($container->getEntityContentType()) . 'CountsQuery';
        if (!\method_exists($this, $methodName))
        {
            return [];
        }

        $query = $this->{$methodName}($container, $messages, $page);
        if (!$query)
        {
            return [];
        }

        $fromCache = $this->getContainerCountsFromCache($container, $query);
        if ($fromCache)
        {
            return $fromCache;
        }

        $results = $this->db()->fetchPairs($query);
        $this->cacheContainerCounts($container, $query, $results);

        return $results;
    }

    /**
     * @param ContainerEntityInterface|Entity|ExtendedThreadEntity $container
     * @param ArrayCollection|array|ExtendedPostEntity[] $messages
     * @param int $page
     *
     * @return string
     */
    protected function getThreadCountsQuery(ContainerEntityInterface $container, /** @noinspection PhpUnusedParameterInspection */array $messages, int $page) : string
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

        if ($this->isSignatureShownOncePerPage($container))
        {
            $pageCondition = "AND post_tmp.position >= {$startQuoted}";
        }
        else
        {
            $pageCondition = 'AND post_tmp.position < post_main.position';
        }

        return "
            SELECT post_main.post_id,
                   (SELECT post_id 
                   FROM xf_post AS post_tmp
                   WHERE post_tmp.user_id = post_main.user_id
                      {$pageCondition}
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
                  AND post.position <= {$endQuoted}
            ) AS post_main";
    }

    /**
     * @param ContainerEntityInterface|Entity|ExtendedConversationMasterEntity $container
     * @param ArrayCollection|array|ExtendedConversationMessageEntity[] $messages
     * @param int $page
     *
     * @return string
     */
    protected function getConversationCountsQuery(ContainerEntityInterface $container, array $messages, /** @noinspection PhpUnusedParameterInspection */int $page) : string
    {
        $db = $this->db();

        /**
         * @var ExtendedConversationMessageEntity $firstMessage
         * @var ExtendedConversationMessageEntity $lastMessage
         */
        $firstMessage = \reset($messages);
        $lastMessage = \end($messages);

        $containerId = $db->quote($container->conversation_id);
        $startQuoted = $db->quote($firstMessage->message_id);
        $endQuoted = $db->quote($lastMessage->message_id);

        if ($this->isSignatureShownOncePerPage($container))
        {
            $pageCondition = "AND conversation_message_tmp.message_id >= {$startQuoted}";
        }
        else
        {
            $pageCondition = 'AND conversation_message_tmp.message_id < conversation_message_main.message_id';
        }

        return "
            SELECT
                conversation_message_main.message_id,
                (
                  SELECT message_id 
                  FROM xf_conversation_message AS conversation_message_tmp
                  WHERE conversation_message_tmp.user_id = conversation_message_main.user_id
                    {$pageCondition}
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

    protected function getPostRepo() : PostRepo
    {
        return $this->repository('XF:Post');
    }
}