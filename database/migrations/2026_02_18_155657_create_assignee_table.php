<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssigneeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.assignee', function (Blueprint $table) {
            $table->bigIncrements('codassignee');
            $table->unsignedBigInteger('codperson')->nullable();
            $table->string('relationship')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('codperson')
                ->references('codperson')
                ->on('main.person')
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
        Schema::dropIfExists('system.assignee');
    }
}
