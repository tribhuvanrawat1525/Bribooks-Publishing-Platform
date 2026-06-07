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
        Schema::create('book_versions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('book_id');

            $table->integer('version_number');

            $table->longText('snapshot');

            $table->unsignedBigInteger('created_by');

            $table->timestamps();

            $table->index('book_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_versions');
    }
};
