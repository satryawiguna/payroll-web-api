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

namespace App;

class Application extends \Illuminate\Foundation\Application
{
    function resourcePath($path = ''): string
    {
        return $this->basePath
            .DIRECTORY_SEPARATOR.'src'
            .DIRECTORY_SEPARATOR.'main'
            .DIRECTORY_SEPARATOR.'resources'
            .($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
