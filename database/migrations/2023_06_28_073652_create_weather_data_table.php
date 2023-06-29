<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('latitude', 8, 2);
            $table->decimal('longitude', 8, 2);
            $table->decimal('temperature', 8, 2)->nullable();
            $table->integer('pressure')->nullable();
            $table->integer('humidity')->nullable();
            $table->decimal('temp_min', 8, 2)->nullable();
            $table->decimal('temp_max', 8, 2)->nullable();
            $table->timestamps();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};
