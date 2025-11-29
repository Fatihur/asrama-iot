<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamera', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->integer('floor');
            $table->string('lokasi')->nullable();
            $table->string('image_url');
            $table->string('image_path')->nullable();
            $table->foreignId('riwayat_id')->nullable()->constrained('riwayat')->nullOnDelete();
            $table->enum('type', ['SCHEDULED', 'EVENT', 'MANUAL'])->default('SCHEDULED');
            $table->timestamp('captured_at')->useCurrent();
            $table->timestamps();

            $table->index(['device_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamera');
    }
};
