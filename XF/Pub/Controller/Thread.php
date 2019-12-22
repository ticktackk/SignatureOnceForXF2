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

        if ($response instanceof View)
        {
            /** @var \TickTackk\SignatureOnce\ControllerPlugin\Container $containerPlugin */
            $containerPlugin = $this->plugin('TickTackk\SignatureOnce:Container');
            /** @noinspection PhpUndefinedFieldInspection */
            $containerPlugin->setShowSignature(
                $response,
                'thread',
                'posts',
                $this->filterPage($params->page),
                'XF:Post'
            );
        }

        return $response;
    }
}