<?php
/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationException extends HttpException
{
    public ?string $key;
    public ?array $vars;

    function __construct(int $statusCode, ?string $message = '', \Throwable $previous = null, array $headers = [],
                         ?int $code = 0, ?string $key = null, ?array $vars = null)
    {
        $this->key = $key;
        $this->vars = $vars;

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    function addVariable(string $key, $value)
    {
        if ($this->vars === null) $this->vars = [];
        $this->vars[$key] = $value;
    }

    function getVariable(string $key)
    {
        if ($this->vars === null) return null;
        return $this->vars[$key] ?? null;
    }

}
