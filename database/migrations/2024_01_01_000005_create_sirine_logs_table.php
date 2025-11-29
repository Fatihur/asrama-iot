<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sirine_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['ON', 'OFF', 'AUTO'])->default('AUTO');
            $table->enum('source', ['MANUAL', 'AUTO', 'API'])->default('MANUAL');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('riwayat_id')->nullable()->constrained('riwayat')->nullOnDelete();
            $table->string('device_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sirine_logs');
    }
};
