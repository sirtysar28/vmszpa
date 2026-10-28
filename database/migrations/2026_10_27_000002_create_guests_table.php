<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('guest_code', 20)->unique();      // Kode Tamu, mis. ZPA-8F3K2A
            $table->string('name');                          // Nama Lengkap
            $table->string('phone', 30)->index();            // Nomor HP
            $table->string('company')->nullable();           // Dari Perusahaan
            $table->string('vehicle_type', 20)->nullable();  // Motor | Mobil | Truck
            $table->string('identity_type', 20)->nullable();  // KTP | SIM | Passport | Lainnya
            $table->string('identity_number', 60)->nullable(); // Nomor Identitas
            $table->timestamps();

            $table->index(['name', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
