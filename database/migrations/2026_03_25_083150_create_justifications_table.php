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
        Schema::create('system.justification', function (Blueprint $table) {

            $table->bigIncrements('codjustification');

            // Relaciones
            $table->unsignedBigInteger('codenrollment');
            $table->unsignedBigInteger('codassistance_session')->nullable();
            $table->unsignedBigInteger('coduser_responsible');

            // Tipo de justificación
            // JT = Temporal | JI = Indefinida
            $table->string('type', 2);

            // Motivo
            $table->text('reason');

            $table->timestamps();
            $table->softDeletes();

            // 🔑 FOREIGN KEYS

            $table->foreign('codenrollment')
                ->references('codenrollment')
                ->on('system.enrollment')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('codassistance_session')
                ->references('codassistance_session')
                ->on('system.assistance_session')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('coduser_responsible')
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
        Schema::dropIfExists('system.justification');
    }
};
