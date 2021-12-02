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

namespace App\Core\Controllers;

/**
 * @OA\Info(
 *     title="Smartbiz Payroll API",
 *     description="API documentation for Smartbiz Payroll",
 *     version="0.1.0",
 *     @OA\Contact(email="adisayoga@gmail.com"),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="https://www.apache.org/licenses/LICENSE-2.0",
 *     )
 * )
 *
 * @OA\Server(url=L5_SWAGGER_CONST_HOST, description="Smartbiz Payroll")
 *
 * @OA\Tag(name="Master", description="Master Data")
 * @OA\Tag(name="WorkStructure", description="Work Structure")
 * @OA\Tag(name="Personal", description="Personal Data")
 * @OA\Tag(name="Parameter", description="Parameter Configuration")
 * @OA\Tag(name="Compensation", description="Compensation Administration")
 * @OA\Tag(name="Payroll", description="Payroll Process")
 */
class SwaggerController
{
}
