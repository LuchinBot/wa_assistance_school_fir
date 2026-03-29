<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('main.grade', function (Blueprint $table) {
            $table->bigIncrements('codgrade');
            $table->unsignedBigInteger('codlevel')->nullable();
            $table->string('name_large');
            $table->string('name_short')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('codlevel', 'fk_grade_level')
                ->references('codlevel')
                ->on('main.level')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('main.grade');
    }
}
