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
        Schema::create('system.user_schedule', function (Blueprint $table) {
            $table->bigIncrements('coduser_schedule');

            // Relaciones
            $table->unsignedBigInteger('codschedule');
            $table->unsignedBigInteger('coduser');
            $table->timestamps();
            $table->softDeletes();

            // 🔑 FOREIGN KEYS

            $table->foreign('codschedule')
                ->references('codschedule')
                ->on('system.schedules')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('coduser')
                ->references('coduser')
                ->on('security.user')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.user_schedule');
    }
};
