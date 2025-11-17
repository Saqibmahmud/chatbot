<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('image_gererations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->OnDelete('cascade');
            $table->text('generated_prompt');
            $table->string('image_path');
            $table->string('original_filename');
            $table->integer('file_size');
            $table->string('mime_type');
  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_gererations');
    }
};
