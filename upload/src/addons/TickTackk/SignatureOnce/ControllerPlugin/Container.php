<?php

namespace TickTackk\SignatureOnce\ControllerPlugin;

use XF\ControllerPlugin\AbstractPlugin;
use XF\Mvc\Entity\Repository;
use XF\Mvc\Reply\View;
use TickTackk\SignatureOnce\Repository\ContentInterface;
use TickTackk\SignatureOnce\Repository\ContentTrait;

/**
 * Class Container
 *
 * @package TickTackk\SignatureOnce\ControllerPlugin
 */
class Container extends AbstractPlugin
{
    /**
     * @param View $view
     * @param string $containerKey
     * @param string $messagesKey
     * @param int $page
     * @param string $repoName
     */
    public function setShowSignature(View $view, $containerKey, $messagesKey, $page, $repoName)
    {
        $container = $view->getParam($containerKey);
        $messages = $view->getParam($messagesKey);
        $page = (int) $page;
        if (!$page)
        {
            $page = 1;
        }

        /** @var Repository|ContentInterface|ContentTrait $repo */
        $repo = $this->repository($repoName);
        $repo->setShowSignature($container, $messages, $page);

        $view->setParam($messagesKey, $messages);
    }
}