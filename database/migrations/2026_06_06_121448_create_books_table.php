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
        Schema::create('books', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('author_id');

            $table->string('title');

            $table->text('description')->nullable();

            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'published',
                'rejected'
            ])->default('draft');

            $table->unsignedBigInteger('current_version_id')->nullable();

            $table->timestamps();

            $table->index('author_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
