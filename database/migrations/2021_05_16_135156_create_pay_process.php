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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayProcess extends Migration
{

    public function up()
    {
        Schema::create('pay_process', function (Blueprint $table) {
            $table->unsignedBigInteger('process_id')->autoIncrement();
            $table->unsignedBigInteger('company_id')->index();

            $table->string('process_type', 5)->index()->charset('ascii')->collation('ascii_general_ci')->comment('P: Payroll | R: Retro Pay');
            $table->string('batch_name', 100);
            $table->date('process_date');
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->date('ret_entry_period_start')->nullable()->comment('Untuk retro');
            $table->date('ret_entry_period_end')->nullable()->comment('Untuk retro');
            $table->string('description', 300)->nullable();
            $table->json('filter_info')->nullable();
            $table->boolean('is_validated')->default(0);

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_process', function (Blueprint $table) {
            $table->index(['period_start', 'period_end']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_process');
    }
}
