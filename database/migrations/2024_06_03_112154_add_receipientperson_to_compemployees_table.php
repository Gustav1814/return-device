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
            $table->string('receipient_person',25)->nullable()->after('receipient_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compemployees', function (Blueprint $table) {
            $table->dropColumn('receipient_person');
        });
    }
};
