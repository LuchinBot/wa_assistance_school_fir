<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssistanceSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.assistance_session', function (Blueprint $table) {
            $table->bigIncrements('codassistance_session');
            $table->unsignedBigInteger('codschedule');
            $table->date('date');
            $table->timestamp('time_opening');
            $table->timestamp('time_ending')->nullable();
            $table->timestamps();
            $table->softDeletes();

             // Foreign Keys
            $table->foreign('codschedule')
                ->references('codschedule')
                ->on('system.schedules')
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
        Schema::dropIfExists('system.assistance_session');
    }
}
