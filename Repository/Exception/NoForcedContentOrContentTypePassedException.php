<?php

namespace TickTackk\SignatureOnce\Repository\Exception;

/**
 * @since 2.0.0
 */
class NoForcedContentOrContentTypePassedException extends \InvalidArgumentException
{
    public function __construct($code = 0, \Throwable $previous = null)
    {
        parent::__construct('No forced content or content type provided.', $code, $previous);
    }
}