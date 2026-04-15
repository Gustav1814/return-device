<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('compemployees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders');      
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');      

            $table->string('emp_first_name',30)->nullable();
            $table->string('emp_last_name',30)->nullable();
            $table->string('emp_email',60)->nullable();
            $table->string('emp_phone',25)->nullable();
            $table->string('emp_add_1',100)->nullable();
            $table->string('emp_add_2',100)->nullable();
            $table->string('emp_city',40)->nullable();
            $table->string('emp_state',50)->nullable();
            $table->string('emp_pcode',50)->nullable();
            $table->string('emp_instruction')->nullable();
            $table->json('emp_otherfields')->nullable();
            $table->string('type_of_equip')->nullable();
            $table->string('return_service')->nullable();
            $table->string('receipient_name',120)->nullable();
            $table->string('receipient_email',120)->nullable();
            $table->string('receipient_phone',50)->nullable();
            $table->string('receipient_add_1',200)->nullable();
            $table->string('receipient_add_2',200)->nullable();
            $table->string('receipient_city',50)->nullable();
            $table->string('receipient_state',50)->nullable();
            $table->string('receipient_zip',50)->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compemployees');
    }
};
