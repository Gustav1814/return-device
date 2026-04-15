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
        Schema::create('labeltracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suborder_id');
            $table->foreign('suborder_id')->references('id')->on('compemployees');
            $table->string('tracking_id',50)->nullable();
            $table->char('flag',10)->nullable();
            $table->string('response_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labeltracking');
    }
};
