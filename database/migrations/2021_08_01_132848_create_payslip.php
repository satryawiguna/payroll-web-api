<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslip extends Migration
{
    public function up()
    {
        Schema::create('pay_payslip_group', function (Blueprint $table) {
            $table->char('group_id', 14)->charset('ascii')->collation('ascii_bin')->primary();
            $table->unsignedBigInteger('company_id')->index();

            $table->string('group_name', 100);
            $table->string('group_type', 5)->charset('ascii')->collation('ascii_general_ci')->comment('+: Earning | -: Deduction');
            $table->boolean('hide_when_empty')->default(1);
            $table->integer('seq_no');

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::create('pay_payslip', function (Blueprint $table) {
            $table->char('payslip_id', 14)->charset('ascii')->collation('ascii_bin')->primary();
            $table->unsignedBigInteger('company_id')->index();
            $table->char('group_id', 14)->charset('ascii')->collation('ascii_bin');

            $table->char('element_id', 14)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->char('balance_id', 14)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->boolean('hide_when_empty')->default(1);
            $table->string('label', 100)->nullable();
            $table->integer('seq_no');

            $table->string('created_by', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('updated_by', 100)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

       Schema::table('pay_payslip', function (Blueprint $table) {
           $table->foreign('group_id')->references('group_id')->on('pay_payslip_group')
                 ->cascadeOnUpdate()->restrictOnDelete();
       });
    }

    public function down()
    {
        Schema::dropIfExists('pay_payslip');
        Schema::dropIfExists('pay_payslip_group');
    }
}
