<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->integer('floor');
            $table->string('event_type'); // SMOKE, SOS, MOTION, NORMAL
            $table->string('value')->nullable();
            $table->string('image_url')->nullable();
            $table->string('notif_channel')->default('WEB');
            $table->enum('sirine_status', ['ON', 'OFF'])->default('OFF');
            $table->enum('ack_status', ['PENDING', 'ACK'])->default('PENDING');
            $table->enum('resolve_status', ['OPEN', 'RESOLVED'])->default('OPEN');
            $table->foreignId('ack_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ack_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();

            $table->index(['event_type', 'timestamp']);
            $table->index('resolve_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat');
    }
};
