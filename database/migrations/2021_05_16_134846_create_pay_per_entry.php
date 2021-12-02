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

class CreatePayPerEntry extends Migration
{

    public function up()
    {
        Schema::create('pay_per_entry', function (Blueprint $table) {
            $table->char('entry_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->unsignedBigInteger('employee_id')->index();
            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('ref_retro_result_id')->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_per_entry', function (Blueprint $table) {
            $table->primary(['entry_id', 'effective_start']);

            $table->foreign('element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('pay_per_entry_value', function (Blueprint $table) {
            $table->char('value_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->char('entry_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->char('input_value_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('entry_value', 100)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_per_entry_value', function (Blueprint $table) {
            $table->primary(['value_id', 'effective_start']);

            $table->foreign('entry_id')->references('entry_id')->on('pay_per_entry')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('input_value_id')->references('input_value_id')->on('pay_input_value')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_per_entry_value');
        Schema::dropIfExists('pay_per_entry');
    }
}
