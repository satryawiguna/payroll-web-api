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

namespace App\Core\Auth;

use App\Core\Models\AbstractModel;

class UserCompany extends AbstractModel
{
    protected $table = SB_PREFIX.'user_companies';

}
