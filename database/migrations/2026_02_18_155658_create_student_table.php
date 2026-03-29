<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.student', function (Blueprint $table) {
            $table->bigIncrements('codstudent');

            $table->unsignedBigInteger('codperson')->nullable();
            $table->unsignedBigInteger('codassignee')->nullable();
            $table->string('carnet')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('codperson')
                ->references('codperson')
                ->on('main.person')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('codassignee')
                ->references('codassignee')
                ->on('system.assignee')
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
        Schema::dropIfExists('system.student');
    }
}
