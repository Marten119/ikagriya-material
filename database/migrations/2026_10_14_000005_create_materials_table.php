<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_code', 40)->unique();
            $table->string('material_name', 150);
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('default_unit_id')->nullable()->constrained('units')->restrictOnDelete();
            $table->string('status', 10)->default('aktif');
            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index('material_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
