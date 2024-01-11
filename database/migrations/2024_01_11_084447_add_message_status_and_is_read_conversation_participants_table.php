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
        //
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->boolean('is_read')->nullable();
            $table->string('message_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
           Schema::table('conversation_participants', function (Blueprint $table) {
               $table->dropColumn('is_read');
               $table->dropColumn('message_status');
           });
    }
};
