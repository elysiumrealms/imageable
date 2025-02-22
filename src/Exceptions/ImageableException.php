<?php

namespace Elysiumrealms\Imageable\Exceptions;

class ImageableException extends \Exception
{
    public function __construct(
        $message,
        $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
