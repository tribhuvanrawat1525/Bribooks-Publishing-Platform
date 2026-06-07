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
        Schema::create('restricted_words', function (Blueprint $table) {

            $table->id();

            $table->string('word');

            $table->enum('type', [
                'profanity',
                'restricted'
            ]);

            $table->timestamps();

            $table->unique('word');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restricted_words');
    }
};
