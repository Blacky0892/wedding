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
        Schema::create('wedding_media', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('disk_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type', 100);
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size');
            $table->string('type', 20)->index();
            $table->string('status', 20)->default('uploaded')->index();
            $table->timestamp('uploaded_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_media');
    }
};
