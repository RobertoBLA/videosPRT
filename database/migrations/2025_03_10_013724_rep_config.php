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
        Schema::create('repconfig', function (Blueprint $table) {
            $table->id();
            $table->boolean('autoplay')->default(true); // Default value true
            $table->boolean('loop')->default(true);    // Default value true
            $table->boolean('auto_next')->default(true); // Default value true
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
