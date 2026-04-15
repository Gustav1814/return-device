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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('company_name',120)->nullable();
            $table->string('company_domain',120)->nullable();
            $table->string('receipient_name',35)->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_add_1',200)->nullable();
            $table->string('company_add_2',200)->nullable();
            $table->string('company_phone',50)->nullable();
            $table->string('company_city',50)->nullable();
            $table->string('company_state',50)->nullable();
            $table->string('company_zip',50)->nullable();
            $table->integer('parent_company')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
