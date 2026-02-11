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
        Schema::create('manual_page_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_page_info_id')->constrained('manual_page_info')->onDelete('cascade');
            $table->string('lang');
            $table->longText('content');
            $table->timestamps();
            $table->softDeletes();

            // Composite unique index for (manual_page_info_id, lang)
            $table->unique(['manual_page_info_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_page_content');
    }
};
