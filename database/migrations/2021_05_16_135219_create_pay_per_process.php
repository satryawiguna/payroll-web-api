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

class CreatePayPerProcess extends Migration
{

    public function up()
    {
        Schema::create('pay_per_process', function (Blueprint $table) {
            $table->unsignedBigInteger('per_process_id')->autoIncrement();
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('process_id');
            $table->unsignedBigInteger('employee_id')->index();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->string('process_status', 5)->index()->charset('ascii')->collation('ascii_general_ci')->comment('N: New Data | S: Success | W: Warning | F: Failed');
            $table->boolean('is_validated')->default(0)->index();
            $table->char('salary_basis_id', 14)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->decimal('basic_salary', 17, 2)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_per_process', function (Blueprint $table) {
            $table->foreign('process_id')->references('process_id')->on('pay_process')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('salary_basis_id')->references('salary_basis_id')->on('pay_salary_basis')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->unique(['process_id', 'employee_id']);
        });

        Schema::create('pay_per_process_result', function (Blueprint $table) {
            $table->unsignedBigInteger('result_id')->autoIncrement();
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('per_process_id');
            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('element_code', 20)->charset('ascii')->collation('ascii_general_ci')->index();
            $table->integer('element_seq_no')->default(1);
            $table->char('parent_element_id', 14)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->char('ref_entry_id', 14)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->decimal('pay_value', 17, 2)->default(0.0);
            $table->decimal('retro_value', 17, 2)->nullable();
            $table->float('division')->nullable();
            $table->string('division_type', 20)->nullable()->charset('ascii')->collation('ascii_general_ci')->comment('value_code di pay_input_value');
            $table->string('description', 100)->nullable();

            $table->boolean('retro_has_processed')->nullable()->index();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_per_process_result', function (Blueprint $table) {
            $table->foreign('per_process_id')->references('per_process_id')->on('pay_per_process')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('parent_element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->nullOnDelete();

            $table->foreign('ref_entry_id')->references('entry_id')->on('pay_per_entry')
                  ->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::create('pay_per_process_result_value', function (Blueprint $table) {
            $table->unsignedBigInteger('value_id')->autoIncrement();
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('result_id');
            $table->char('input_value_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('value_code', 20)->index()->charset('ascii')->collation('ascii_general_ci')->comment('value_code di pay_input_value');
            $table->string('value', 100)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_per_process_result_value', function (Blueprint $table) {
            $table->foreign('result_id')->references('result_id')->on('pay_per_process_result')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('input_value_id')->references('input_value_id')->on('pay_input_value')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::table('pay_per_entry', function (Blueprint $table) {
            $table->foreign('ref_retro_result_id')->references('result_id')->on('pay_per_process_result')
                  ->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('pay_per_entry', function (Blueprint $table) {
            $table->dropForeign('pay_per_entry_ref_retro_result_id_foreign');
        });
        Schema::dropIfExists('pay_per_process_result_dtl');
        Schema::dropIfExists('pay_per_process_result');
        Schema::dropIfExists('pay_per_process');
    }
}
