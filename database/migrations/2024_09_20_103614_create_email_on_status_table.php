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
        Schema::create('email_on_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suborder_id');
            $table->foreign('suborder_id')->references('id')
            ->on('compemployees');
            $table->integer('box_del_emp')->default(0);
            $table->date('box_del_emp_dt')->nullable();
            $table->integer('device_del_start')->default(0);  
            $table->date('device_del_start_dt')->nullable();
            $table->integer('device_del_comp')->default(0);  
            $table->date('device_del_comp_dt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_on_status');
    }
};
