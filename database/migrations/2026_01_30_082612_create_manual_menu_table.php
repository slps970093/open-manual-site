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
        Schema::create('manual_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_id')->constrained('manual')->onDelete('cascade');
            $table->foreignId('manual_page_info_id')->nullable()->constrained('manual_page_info')->onDelete('set null');
            $table->json('name');
            $table->enum('click_action', ['external', 'page', 'expand'])->default('expand');
            $table->string('url')->default('');
            $table->integer('parent_id')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_menu');
    }
};
