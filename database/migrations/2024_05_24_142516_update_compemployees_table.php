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
            $table->char('send_flag', length: 2);  
            $table->char('rec_flag', length: 2);  
            $table->text('send_labelresponse')->nullable();
            $table->text('receive_labelresponse')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compemployees', function (Blueprint $table) {
            $table->dropColumn('send_flag');
            $table->dropColumn('rec_flag');
            $table->dropColumn('send_labelresponse');
            $table->dropColumn('receive_labelresponse');
        });
    }
};
