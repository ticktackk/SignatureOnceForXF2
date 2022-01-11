<?php

namespace TickTackk\SignatureOnce\Entity\Exception;

use Throwable;

/**
 * @since 2.0.0 Alpha 1
 */
class NoViewingPageSetException extends \InvalidArgumentException
{
    /**
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('No viewing page set.', $code, $previous);
    }
}