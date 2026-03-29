<?php
// database/migrations/xxxx_create_grade_schedule_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system.grade_schedule', function (Blueprint $table) {
            $table->bigIncrements('codgrade_schedule');
            $table->unsignedInteger('codgrade');
            $table->unsignedInteger('codschedule');
            $table->string('section', 25);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('codgrade')
                ->references('codgrade')
                ->on('main.grade')
                ->onDelete('cascade');

            $table->foreign('codschedule')
                ->references('codschedule')
                ->on('system.schedules')
                ->onDelete('cascade');

            // Un grado no puede tener el mismo horario dos veces
            $table->unique(['codgrade', 'codschedule', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system.grade_schedule');
    }
};
