<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pterodactyl_nodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('node_id');
            $table->unsignedBigInteger('location_id');
            $table->string('name')->nullable();
            $table->string('fqdn')->nullable();
            $table->string('ip')->nullable();
            $table->string('ports_range')->default('49152-65535');
            $table->integer('auto_ports')->default(1);
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('pterodactyl_nodes');
    }
};
