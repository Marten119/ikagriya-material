<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_code', 10)->unique();
            $table->string('unit_name', 60);
            $table->string('status', 10)->default('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
