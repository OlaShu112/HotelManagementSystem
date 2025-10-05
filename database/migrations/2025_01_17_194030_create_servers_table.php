<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eoservers', function (Blueprint $table) {
            $table->id();
            $table->string('server_address')->nullable();
            $table->string('ip_address')->nullable(); // Server IP address
            $table->unsignedBigInteger('total_users')->default(0); // Total users on the server
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
