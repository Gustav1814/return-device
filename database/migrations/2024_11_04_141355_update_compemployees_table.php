<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('compemployees', function (Blueprint $table) {
            $table->integer('return_additional_srv')->after('soft_del')->nullable();
            $table->json('new_emp_data')->after('return_additional_srv')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compemployees', function (Blueprint $table) {
            $table->dropColumn('return_additional_srv');
            $table->dropColumn('new_emp_data');
        });
    }
};
