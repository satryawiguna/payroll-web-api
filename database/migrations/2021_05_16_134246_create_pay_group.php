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

class CreatePayGroup extends Migration
{

    public function up()
    {
        Schema::create('pay_group', function (Blueprint $table) {
            $table->char('pay_group_id', 14)->charset('ascii')->collation('ascii_bin');
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->date('effective_first')->default('1000-01-01')->index();
            $table->date('effective_start')->default('1000-01-01')->index();
            $table->date('effective_end')->default('9000-12-31')->index();
            $table->date('effective_last')->default('9000-12-31')->index();

            $table->string('pay_group_name', 100);
            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::table('pay_group', function (Blueprint $table) {
            $table->primary(['pay_group_id', 'effective_start']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_group');
    }
}
