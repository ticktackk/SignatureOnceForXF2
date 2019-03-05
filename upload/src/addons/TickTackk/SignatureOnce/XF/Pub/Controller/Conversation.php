<?php

namespace TickTackk\SignatureOnce\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

/**
 * Class Conversation
 * 
 * Extends \XF\Pub\Controller\Conversation
 *
 * @package TickTackk\SignatureOnce\XF\Pub\Controller
 */
class Conversation extends XFCP_Conversation
{
    /**
     * @param ParameterBag $params
     *
     * @return View
     */
    public function actionView(ParameterBag $params)
    {
        $response = parent::actionView($params);

        if ($response instanceof View)
        {
            /** @var \TickTackk\SignatureOnce\ControllerPlugin\Container $containerPlugin */
            $containerPlugin = $this->plugin('TickTackk\SignatureOnce:Container');
            /** @noinspection PhpUndefinedFieldInspection */
            $containerPlugin->setShowSignature(
                $response,
                'conversation',
                'messages',
                $this->filterPage($params->page),
                'XF:ConversationMessage'
            );
        }

        return $response;
    }
}