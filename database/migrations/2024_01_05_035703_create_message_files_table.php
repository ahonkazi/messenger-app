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
        Schema::create('message_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('single_message_id')->constrained('single_messages');
            $table->string('caption')->nullable();
            $table->string('file_type');
            $table->foreignId('media_id')->constrained('media');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_files');
    }
};
