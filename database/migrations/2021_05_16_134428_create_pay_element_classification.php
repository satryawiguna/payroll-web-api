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

class CreatePayElementClassification extends Migration
{

    public function up()
    {
        Schema::create('pay_element_classification', function (Blueprint $table) {
            $table->char('classification_id', 14)->charset('ascii')->collation('ascii_bin')->primary();
            $table->unsignedBigInteger('company_id')->nullable()->index();

            $table->string('classification_name', 100);
            $table->integer('default_priority')->index();
            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pay_element_classification');
    }
}
