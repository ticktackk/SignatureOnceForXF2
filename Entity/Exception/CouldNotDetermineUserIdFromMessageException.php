<?php

namespace TickTackk\SignatureOnce\Entity\Exception;

use Throwable;

/**
 * Class CouldNotDetermineUserIdFromMessageException
 *
 * @package TickTackk\SignatureOnce\Entity\Exception
 */
class CouldNotDetermineUserIdFromMessageException extends \InvalidArgumentException
{
    /**
     * CouldNotDetermineUserIdFromMessageException constructor.
     *
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Could not determine message user id.', $code, $previous);
    }
}