<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->string('purpose');                       // Tujuan (wajib)
            $table->string('vehicle_number', 30)->nullable(); // Nomor Kendaraan (wajib)
            $table->string('meet_with')->nullable();         // Bertemu dengan siapa
            $table->text('notes')->nullable();               // Catatan
            $table->timestamp('check_in_at')->index();       // Tgl & Jam Check-In
            $table->timestamp('check_out_at')->nullable()->index(); // Tgl & Jam Check-Out
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['check_in_at', 'check_out_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
