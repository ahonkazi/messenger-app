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
        //
        Schema::table('single_messages', function (Blueprint $table) {
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('receiver_id')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
         Schema::table('single_messages', function (Blueprint $table) {
             $table->dropConstrainedForeignId('sender_id');
             $table->dropConstrainedForeignId('receiver_id');
         });
    }
};
