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

class CreateDbFormulaList extends Migration
{

    public function up()
    {
        Schema::create('db_formula_list', function (Blueprint $table) {
            $table->string('formula_name', 100)->charset('ascii')->collation('ascii_general_ci')->primary();
            $table->string('formula_category', 5)->charset('ascii')->collation('ascii_general_ci')->index()->comment('P: Payroll | A: Attendance | X: Skip Formula');
            $table->json('parameters');
            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('db_formula_list');
    }
}
