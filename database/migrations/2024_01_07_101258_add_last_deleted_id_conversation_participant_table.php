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
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->bigInteger('last_deleted_message_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
          Schema::table('conversation_participants', function (Blueprint $table) {
              $table->dropColumn('last_deleted_message_id');
          });
    }
};
