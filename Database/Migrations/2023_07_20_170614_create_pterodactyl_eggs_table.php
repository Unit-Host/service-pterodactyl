<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pterodactyl_eggs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('egg_id');
            $table->unsignedBigInteger('nest_id');
            $table->text('variables')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eggs');
    }
};
