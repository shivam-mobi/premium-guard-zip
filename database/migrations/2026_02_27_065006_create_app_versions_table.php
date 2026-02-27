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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('app_key', 100);
            $table->string('platform', 20); // ios, android
            $table->string('min_version', 20);
            $table->string('latest_version', 20);
            $table->boolean('force_update')->default(false);
            $table->text('update_message')->nullable();
            $table->string('store_url', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
