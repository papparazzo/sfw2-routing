<?php

namespace SFW2\Routing\Router;

use SFW2\Core\SFW2Exception;

class Exception extends SFW2Exception {
    final public const INVALID_DATA_GIVEN = 400;  // Bad Request
    final public const NO_PERMISSION      = 403;  // Forbidden;
    final public const PAGE_NOT_FOUND     = 404;  // Not Found

    final public const UNKNOWN_ERROR      = 500;  // Internal Server Error

}