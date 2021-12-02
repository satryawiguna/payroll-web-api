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

namespace App\Jobs;

use App\Modules\Payroll\Services\PayrollProcessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PayrollCalculateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $data;

    function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    function handle(PayrollProcessService $payrollSvc)
    {
        $processId = $this->data['process_id'];
        $user = $this->data['user'];

        $payrollSvc->calculatePayroll($processId, $user);
    }
}
