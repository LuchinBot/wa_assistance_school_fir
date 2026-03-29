<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.teacher', function (Blueprint $table) {
           $table->bigIncrements('codteacher');

            $table->unsignedBigInteger('codperson')->nullable();
            $table->unsignedBigInteger('codprofession')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('codperson')
                ->references('codperson')
                ->on('main.person')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('codprofession')
                ->references('codprofession')
                ->on('main.profession')
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
        Schema::dropIfExists('system.teacher');
    }
}
