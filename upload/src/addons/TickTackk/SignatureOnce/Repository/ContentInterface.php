<?php

namespace TickTackk\SignatureOnce\Repository;

use TickTackk\SignatureOnce\Entity\ContainerInterface;
use XF\Mvc\Entity\ArrayCollection;

/**
 * Interface ContentInterface
 *
 * @package TickTackk\SignatureOnce\Repository
 */
interface ContentInterface
{
    /**
     * @return bool
     */
    public function showSignatureOncePerPage();

    /**
     * @param ContainerInterface $container
     * @param ArrayCollection    $messages
     * @param int $page
     *
     * @return array
     */
    public function getMessageCountsForSignatureOnce(ContainerInterface $container, ArrayCollection $messages, $page);
}