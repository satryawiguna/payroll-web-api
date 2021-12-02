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

class CreatePayElement extends Migration
{

    public function up()
    {
        Schema::create('pay_element', function (Blueprint $table) {
            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->string('element_code', 20)->charset('ascii')->collation('ascii_general_ci')->index();
            $table->string('element_name', 100);
            $table->char('classification_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('last_entry_type', 5)->charset('ascii')->collation('ascii_general_ci')->comment('T: Termination | S: Last Standar Process | F: Final Close');
            $table->integer('processing_priority')->index();
            $table->char('retro_element_id', 14)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->boolean('is_recurring')->default(1);
            $table->boolean('is_once_per_period')->default(1);
            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_element', function (Blueprint $table) {
            $table->primary(['element_id', 'effective_start']);

            $table->foreign('classification_id')->references('classification_id')->on('pay_element_classification')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('retro_element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('pay_input_value', function (Blueprint $table) {
            $table->char('input_value_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('value_code', 20)->charset('ascii')->collation('ascii_general_ci')->comment('AMOUNT | RATE | PAY_VALUE | …');
            $table->string('value_name', 100);
            $table->string('default_value', 100)->nullable();
            $table->string('data_type', 5)->charset('ascii')->collation('ascii_general_ci')->comment('C: Character | N: Number | D: Date');
            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_input_value', function (Blueprint $table) {
            $table->primary(['input_value_id', 'effective_start']);

            $table->foreign('element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_input_value');
        Schema::dropIfExists('pay_element');
    }
}
