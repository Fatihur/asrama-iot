<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('settings')->insert([
            ['key' => 'sirine_mode', 'value' => 'AUTO', 'type' => 'string', 'group' => 'sirine', 'description' => 'Mode sirine: ON, OFF, AUTO', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'telegram_bot_token', 'value' => '', 'type' => 'string', 'group' => 'notification', 'description' => 'Token bot Telegram', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'telegram_chat_id', 'value' => '', 'type' => 'string', 'group' => 'notification', 'description' => 'Chat ID Telegram', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'whatsapp_api_url', 'value' => '', 'type' => 'string', 'group' => 'notification', 'description' => 'URL API WhatsApp', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'app_name', 'value' => 'Asrama IoT Monitoring', 'type' => 'string', 'group' => 'general', 'description' => 'Nama aplikasi', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
