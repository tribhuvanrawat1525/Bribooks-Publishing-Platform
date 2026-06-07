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
        Schema::create('api_logs', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id')
                ->nullable();

            $table->string('method',20);

            $table->text('url');

            $table->string('ip_address',50)
                ->nullable();

            $table->longText('request_headers')
                ->nullable();

            $table->longText('request_body')
                ->nullable();

            $table->longText('response_body')
                ->nullable();

            $table->integer('status_code')
                ->nullable();

            $table->decimal('execution_time_ms',10,2)
                ->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('method');
            $table->index('status_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
