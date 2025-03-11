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
        Schema::create('validation_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')
                ->constrained('import_files')
                ->onDelete('cascade');
            $table->integer('row_number')->nullable(false);
            $table->text('error_message')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validation_errors');
    }
};
