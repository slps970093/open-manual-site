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
        Schema::create('manual', function (Blueprint $table) {
            $table->id();
            $table->string('url_slug')->unique()->comment('URL-friendly identifier for the manual');
            $table->json('name')->comment('Translatable manual name');
            $table->json('description')->nullable()->comment('Translatable manual description');
            $table->boolean('is_public')->default(false)->comment('Whether the manual is publicly accessible');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual');
    }
};
