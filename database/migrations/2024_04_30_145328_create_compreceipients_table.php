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
        Schema::create('compreceipients', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('company_id');
            $table->string('receipient_name',120)->nullable();
            $table->string('receipient_email',120)->nullable();
            $table->string('receipient_phone',50)->nullable();
            $table->string('receipient_add_1',200)->nullable();
            $table->string('receipient_add_2',200)->nullable();
            $table->string('receipient_city',50)->nullable();
            $table->string('receipient_state',50)->nullable();
            $table->string('receipient_zip',50)->nullable(); 
            $table->string('order_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compreceipients');
    }
};
