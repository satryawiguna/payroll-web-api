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

class CreatePaySalaryBasis extends Migration
{

    public function up()
    {
        Schema::create('pay_salary_basis', function (Blueprint $table) {
            $table->char('salary_basis_id', 14)->charset('ascii')->collation('ascii_bin')->primary();
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->string('salary_basis_code', 20)->charset('ascii')->collation('ascii_general_ci')->index();
            $table->string('salary_basis_name', 100);
            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->char('input_value_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_salary_basis', function (Blueprint $table) {
            $table->foreign('element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('input_value_id')->references('input_value_id')->on('pay_input_value')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_salary_basis');
    }
}
