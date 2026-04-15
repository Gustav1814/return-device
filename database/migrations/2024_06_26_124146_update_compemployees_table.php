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
        Schema::table('compemployees', function (Blueprint $table) {
            $table->char('insurance_active',2)->default(0)->after('emp_otherfields')->nullable(); 
            $table->integer('insurance_amount')->after('insurance_active')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compemployees', function (Blueprint $table) {
            $table->dropColumn('insurance_active');
            $table->dropColumn('insurance_amount');
        });
    }
};
