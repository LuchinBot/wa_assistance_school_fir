<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrollmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.enrollment', function (Blueprint $table) {

            $table->bigIncrements('codenrollment');

            $table->unsignedBigInteger('codstudent')->nullable();
            $table->unsignedBigInteger('codgrade_schedule')->nullable();
            $table->unsignedBigInteger('codperiod')->nullable();

            $table->char('status', 1)->default('A');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['codstudent', 'codperiod']);

            // Foreign Keys
            $table->foreign('codstudent')
                ->references('codstudent')
                ->on('system.student')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('codgrade_schedule')
                ->references('codgrade_schedule')
                ->on('system.grade_schedule')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('codperiod')
                ->references('codperiod')
                ->on('system.period')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system.enrollment');
    }
}
