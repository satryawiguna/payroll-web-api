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

class CreatePayFormula extends Migration
{

    public function up()
    {
        Schema::create('pay_formula', function (Blueprint $table) {
            $table->char('formula_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->string('formula_name', 100);
            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('formula_type', 5)->index()->charset('ascii')->collation('ascii_general_ci')->comment('SP: Stored Procedure | FX: Simple Formula');
            $table->string('formula_def', 100)->charset('ascii')->collation('ascii_general_ci')->nullable();
            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_formula', function (Blueprint $table) {
            $table->primary(['formula_id', 'effective_start']);

            $table->foreign('element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('pay_formula_result', function (Blueprint $table) {
            $table->char('result_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->char('formula_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('result_code', 20)->charset('ascii')->collation('ascii_general_ci')->comment('Nama out parameter dari stored procedure');
            $table->string('formula_expr', 100)->charset('ascii')->collation('ascii_general_ci')->nullable();
            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->char('input_value_id', 14)->charset('ascii')->collation('ascii_bin');

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_formula_result', function (Blueprint $table) {
            $table->primary(['result_id', 'effective_start']);

            $table->foreign('formula_id')->references('formula_id')->on('pay_formula')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('input_value_id')->references('input_value_id')->on('pay_input_value')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_formula_result');
        Schema::dropIfExists('pay_formula');
    }
}
