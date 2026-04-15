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
            $table->char('dest_flag', length: 2)->after('rec_flag')->nullable();
            $table->text('dest_labelresponse')->after('receive_labelresponse')->nullable();
            $table->string('dest_label_status', 25)->after('receive_label_status')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compemployees', function (Blueprint $table) {
            $table->dropColumn('dest_flag');
            $table->dropColumn('dest_labelresponse');
            $table->dropColumn('dest_label_status');
        });
    }
};
