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
        Schema::create('book_uploads', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('book_id');

            $table->string('file_name');

            $table->string('file_path');

            $table->string('file_type');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_uploads');
    }
};
