<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riwayat_id')->constrained('riwayat')->cascadeOnDelete();
            $table->foreignId('kontak_id')->nullable()->constrained('kontaks')->nullOnDelete();
            $table->string('channel'); // WEB, WHATSAPP, TELEGRAM, EMAIL
            $table->string('recipient')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['PENDING', 'SENT', 'FAILED', 'DELIVERED'])->default('PENDING');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['riwayat_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusi');
    }
};
