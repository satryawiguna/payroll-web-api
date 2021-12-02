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

class CreatePayPerProcessLog extends Migration
{

    public function up()
    {
        Schema::create('pay_process_log', function (Blueprint $table) {
            $table->unsignedBigInteger('log_id')->autoIncrement();
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('process_id');
            $table->unsignedBigInteger('per_process_id')->nullable();
            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin')->nullable();

            $table->string('key', 50)->nullable();
            $table->string('description', 300)->nullable();
            $table->string('severity', 5)->charset('ascii')->collation('ascii_general_ci')->comment('I: Info | W: Warning | E: Error');
            $table->text('exception_info')->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_process_log', function (Blueprint $table) {
            $table->foreign('process_id')->references('process_id')->on('pay_process')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('per_process_id')->references('per_process_id')->on('pay_per_process')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_per_process_result_dtl');
    }
}
