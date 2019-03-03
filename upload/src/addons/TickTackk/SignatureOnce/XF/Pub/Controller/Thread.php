<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

/**
 * Class Thread
 * 
 * Extends \XF\Pub\Controller\Thread
 *
 * @package TickTackk\SignatureOnce\XF\Pub\Controller
 */
class Thread extends XFCP_Thread
{
    /**
     * @param ParameterBag $params
     *
     * @return View
     */
    public function actionIndex(ParameterBag $params)
    {
        $response = parent::actionIndex($params);

        if ($response instanceof View && $posts = $response->getParam('posts'))
        {
            /** @var \TickTackk\SignatureOnce\XF\Repository\Post $postRepo */
            $postRepo = $this->repository('XF:Post');

            /** @noinspection PhpUndefinedFieldInspection */
            $page = $this->filterPage($params->page);
            $posts = $postRepo->setPostsShowSignature($posts, $page);

            $response->setParam('posts', $posts);
        }

        return $response;
    }
}