<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('main.person', function (Blueprint $table) {
            $table->bigIncrements('codperson');
            $table->integer('codtd_identify')->nullable();
            $table->integer('codubigeo')->nullable();
            $table->integer('codgender')->nullable();
            $table->integer('codcivil_status')->nullable();
            $table->string('identify_number');
            $table->string('firstname');
            $table->string('lastname_father');
            $table->string('lastname_mom');
            $table->string('address')->nullable();
            $table->string("department")->nullable();
            $table->string("province")->nullable();
            $table->string("district")->nullable();
            $table->string("phone", 80)->nullable();
            $table->string("email", 90)->nullable();
            $table->date("birthday")->nullable();
            $table->string("nationality")->nullable();
            $table->string("photo")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('main.person');
    }
}
