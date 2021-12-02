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

class CreatePayElementLink extends Migration
{

    public function up()
    {
        Schema::create('pay_element_link', function (Blueprint $table) {
            $table->char('link_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin');

            $table->unsignedBigInteger('office_id')->index()->nullable();
            $table->unsignedBigInteger('location_id')->index()->nullable();
            $table->unsignedBigInteger('department_id')->index()->nullable();
            $table->unsignedBigInteger('project_id')->index()->nullable();
            $table->unsignedBigInteger('position_id')->index()->nullable();
            $table->unsignedBigInteger('grade_id')->index()->nullable();
            $table->char('pay_group_id', 14)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('people_group', 5)->nullable()->index()->charset('ascii')->collation('ascii_general_ci')->comment('1: Kantor Pusat | 2: Kantor Cabang');
            $table->string('employee_category', 5)->nullable()->index()->charset('ascii')->collation('ascii_general_ci')->comment('F: Full Time | P: Part Time | C: Contract | O: Outsource');
            $table->unsignedBigInteger('employee_id')->index()->nullable();

            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_element_link', function (Blueprint $table) {
            $table->primary(['link_id', 'effective_start']);

            $table->foreign('element_id')->references('element_id')->on('pay_element')
                  ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('pay_group_id')->references('pay_group_id')->on('pay_group')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('pay_element_link_value', function (Blueprint $table) {
            $table->char('value_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->char('link_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->char('input_value_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->string('link_value', 100)->nullable();

            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_element_link_value', function (Blueprint $table) {
            $table->primary(['value_id', 'effective_start']);

            $table->foreign('link_id')->references('link_id')->on('pay_element_link')
                  ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign('input_value_id')->references('input_value_id')->on('pay_input_value')
                  ->cascadeOnUpdate()->restrictOnDelete();
        });

    }

    public function down()
    {
        Schema::dropIfExists('pay_element_link_value');
        Schema::dropIfExists('pay_element_link');
    }
}
