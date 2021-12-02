<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerBasicSalary extends Migration
{
    public function up()
    {
        Schema::create('per_basic_salary', function (Blueprint $table) {
            $table->char('salary_id', 14)->charset('ascii')->collation('ascii_bin')->primary();
            $table->unsignedBigInteger('company_id')->index();

            $table->unsignedBigInteger('employee_id')->index();
            $table->date('change_date')->default('1000-01-01')->index();
            $table->date('date_to')->default('9000-12-31')->index();
            $table->decimal('basic_salary', 17, 2);
            $table->string('change_reason', 5)->charset('ascii')->collation('ascii_general_ci')->comment('N: New Hire | P: Promotion');
            $table->string('description', 300)->nullable();

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('per_basic_salary');
    }
}
