<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontaks', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('nomor');
            $table->string('whatsapp')->nullable();
            $table->text('pesan_wa')->nullable();
            $table->string('telegram_id')->nullable();
            $table->string('email')->nullable();
            $table->string('ikon')->default('user');
            $table->boolean('status')->default(true);
            $table->boolean('notify_smoke')->default(true);
            $table->boolean('notify_sos')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontaks');
    }
};
