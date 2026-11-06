<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 30)->unique();
            $table->date('transaction_date');                       // tanggal material masuk (kejadian)
            $table->foreignId('cluster_id')->constrained('clusters')->restrictOnDelete();
            $table->foreignId('material_id')->constrained('materials')->restrictOnDelete();
            $table->decimal('volume', 14, 3);
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('usage_per_unit', 14, 3)->default(0);
            $table->decimal('usage_m2', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('submit_token', 64)->nullable()->unique(); // anti transaksi ganda
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();                                    // created_at = tanggal pencatatan

            $table->index('transaction_date');
            $table->index(['cluster_id', 'transaction_date']);
            $table->index(['material_id', 'transaction_date']);
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_receipts');
    }
};
